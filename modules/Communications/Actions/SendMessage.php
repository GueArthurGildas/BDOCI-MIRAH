<?php

namespace Modules\Communications\Actions;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageTrigger;
use App\Models\MessageTemplate;
use App\Models\SentMessage;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Modules\Communications\Support\MessageDispatcher;
use Modules\Communications\Support\MessageRenderer;

class SendMessage extends Action
{
    public function __construct(private readonly MessageDispatcher $dispatcher, private readonly MessageRenderer $renderer) {}

    public function code(): string
    {
        return 'communications.send_message';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsSend;
    }

    public function handle(ActionContext $context, array $input): SentMessage
    {
        $template = MessageTemplate::findOrFail($input['message_template_id']);
        $vessel = isset($input['vessel_id']) ? Vessel::find($input['vessel_id']) : null;
        $channel = CommunicationChannelCode::from($input['channel']);
        $rendered = $this->renderer->render($template, $vessel);

        $this->dispatcher->assertCanDeliver($channel, $input['recipient']);

        $message = $this->dispatcher->prepare($channel, [
            'message_template_id' => $template->id,
            'vessel_id' => $vessel?->id,
            'recipient' => $input['recipient'],
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'triggered_by' => MessageTrigger::User->value,
            'user_id' => $context->actor->id,
        ]);

        $this->dispatcher->deliver($message);

        if ($vessel) {
            $context->publish(
                vessel: $vessel,
                module: 'communications',
                type: "message.{$message->status->value}",
                subject: $message,
                summaryKey: "communications::events.message_{$message->status->value}",
                params: ['template' => $template->code, 'recipient' => $message->recipient],
            );
        }

        return $message;
    }
}
