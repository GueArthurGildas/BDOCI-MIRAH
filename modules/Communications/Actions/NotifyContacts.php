<?php

namespace Modules\Communications\Actions;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageTrigger;
use App\Models\Contact;
use App\Models\Document;
use App\Models\LogbookDay;
use App\Models\MessageTemplate;
use App\Models\Observation;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use App\Support\Documents\GeneratedDocuments;
use Illuminate\Support\Collection;
use Modules\Communications\Support\MessageDispatcher;
use Modules\Communications\Support\NotificationResult;

class NotifyContacts extends Action
{
    public function __construct(
        private readonly MessageDispatcher $dispatcher,
        private readonly DocumentStore $documents,
        private readonly GeneratedDocuments $generated,
    ) {}

    public function code(): string
    {
        return 'communications.notify_contacts';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsSend;
    }

    public function handle(ActionContext $context, array $input): NotificationResult
    {
        $channel = CommunicationChannelCode::from($input['channel']);
        $template = ! empty($input['message_template_id']) ? MessageTemplate::find($input['message_template_id']) : null;
        $vessel = ! empty($input['vessel_id']) ? Vessel::find($input['vessel_id']) : null;
        $attachments = $this->attachments($context, $input, $vessel);

        $messages = collect();
        $skipped = [];

        foreach (Contact::whereIn('id', $input['contact_ids'])->orderBy('organization')->get() as $contact) {
            $recipient = (string) ($channel === CommunicationChannelCode::Email ? $contact->email : $contact->phone);

            if ($recipient === '') {
                $skipped[] = ['contact' => $contact, 'reason' => "missing_{$channel->value}"];

                continue;
            }

            if (! $this->dispatcher->canDeliver($channel, $recipient)) {
                $skipped[] = ['contact' => $contact, 'reason' => 'consent_missing'];

                continue;
            }

            $message = $this->dispatcher->prepare($channel, [
                'message_template_id' => $template?->id,
                'vessel_id' => $vessel?->id,
                'recipient' => $recipient,
                'subject' => $input['subject'] ?? null,
                'body' => $input['body'],
                'triggered_by' => MessageTrigger::User->value,
                'user_id' => $context->actor->id,
            ]);

            $attachments->each(fn (Document $document) => $this->documents->attach($document, $message));
            $this->dispatcher->deliver($message);
            $messages->push($message);

            if ($vessel) {
                $context->publish(
                    vessel: $vessel,
                    module: 'communications',
                    type: "message.{$message->status->value}",
                    subject: $message,
                    summaryKey: "communications::events.message_{$message->status->value}",
                    params: ['template' => $template?->code ?? $contact->organization, 'recipient' => $message->recipient],
                );
            }
        }

        return new NotificationResult($messages, $skipped);
    }

    /** @return Collection<int, Document> */
    private function attachments(ActionContext $context, array $input, ?Vessel $vessel): Collection
    {
        $subjects = [
            GeneratedDocuments::VESSEL_SHEET => $vessel,
            GeneratedDocuments::LOGBOOK_DAY => ! empty($input['logbook_day_id']) ? LogbookDay::find($input['logbook_day_id']) : null,
            GeneratedDocuments::OBSERVATION => ! empty($input['observation_id']) ? Observation::find($input['observation_id']) : null,
        ];

        return collect($input['documents'] ?? [])
            ->filter(fn (string $kind) => $subjects[$kind] ?? null)
            ->map(function (string $kind) use ($subjects, $context) {
                $file = $this->generated->make($kind, $subjects[$kind], $context->actor);

                return $this->documents->storeContent($file->name, $file->content, $file->mimeType, $context->actor);
            })
            ->values();
    }
}
