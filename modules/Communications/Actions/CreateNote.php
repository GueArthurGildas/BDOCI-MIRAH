<?php

namespace Modules\Communications\Actions;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageStatus;
use App\Enums\MessageTrigger;
use App\Enums\NoteRelay;
use App\Enums\UserType;
use App\Models\InternalNote;
use App\Models\User;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Modules\Communications\Notifications\NoteReceivedAlert;
use Modules\Communications\Support\MessageDispatcher;

class CreateNote extends Action
{
    public function __construct(private readonly MessageDispatcher $dispatcher) {}

    public function code(): string
    {
        return 'communications.create_note';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsCreateNote;
    }

    public function handle(ActionContext $context, array $input): InternalNote
    {
        $note = InternalNote::create([
            ...Arr::only($input, ['type', 'vessel_id', 'urgency', 'recipient_user_id', 'recipient_team_id', 'due_date', 'free_text', 'ai_agent_action_id']),
            'author_id' => $context->actor->id,
        ]);

        $recipients = $this->recipients($note, $context->actor);
        $recipients->each(fn (User $user) => $user->notify(new NoteReceivedAlert($note->loadMissing(['vessel', 'author']))));

        if (! empty($input['relay']) && $this->relay($note, NoteRelay::from($input['relay']), $recipients, $context->actor)) {
            $note->update(['relayed_by' => $input['relay']]);
        }

        if ($note->vessel_id) {
            $context->publish(
                vessel: $note->vessel_id,
                module: 'communications',
                type: 'note.created',
                subject: $note,
                summaryKey: "communications::events.note.{$note->type->value}",
                params: ['author' => $context->actor->name],
            );
        }

        return $note;
    }

    /** @return Collection<int, User> */
    private function recipients(InternalNote $note, User $author): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereKeyNot($author->id)
            ->where(fn ($query) => $query
                ->when($note->recipient_user_id, fn ($query, $id) => $query->orWhere('id', $id))
                ->when($note->recipient_team_id, fn ($query, $id) => $query->orWhere('team_id', $id)))
            ->get();
    }

    /** @param Collection<int, User> $recipients */
    private function relay(InternalNote $note, NoteRelay $relay, Collection $recipients, User $author): bool
    {
        $channel = CommunicationChannelCode::from($relay->value);
        $triggeredBy = $author->type === UserType::AiAgent ? MessageTrigger::AiAgent : MessageTrigger::User;
        $relayed = false;

        foreach ($recipients as $recipient) {
            $address = $relay === NoteRelay::Email ? $recipient->email : $recipient->whatsapp_number;

            if (! $address || ! $this->dispatcher->canDeliver($channel, $address)) {
                continue;
            }

            $message = $this->dispatcher->prepare($channel, [
                'vessel_id' => $note->vessel_id,
                'recipient' => $address,
                'subject' => __('communications::notes.relay.subject', ['type' => __($note->type->label())]),
                'body' => __('communications::notes.relay.body', [
                    'author' => $author->name,
                    'type' => __($note->type->label()),
                    'vessel' => $note->vessel?->name ?? '-',
                    'text' => $note->free_text ?? '-',
                ]),
                'triggered_by' => $triggeredBy->value,
                'user_id' => $author->id,
            ]);

            $relayed = $this->dispatcher->deliver($message)->status === MessageStatus::Sent || $relayed;
        }

        return $relayed;
    }
}
