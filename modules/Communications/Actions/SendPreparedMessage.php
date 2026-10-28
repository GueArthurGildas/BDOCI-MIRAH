<?php

namespace Modules\Communications\Actions;

use App\Enums\MessageStatus;
use App\Models\SentMessage;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;
use Modules\Communications\Support\MessageDispatcher;

class SendPreparedMessage extends Action
{
    public function __construct(private readonly MessageDispatcher $dispatcher) {}

    public function code(): string
    {
        return 'communications.send_prepared_message';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsSend;
    }

    public function handle(ActionContext $context, array $input): SentMessage
    {
        $message = SentMessage::with('channel')->lockForUpdate()->findOrFail($input['message_id']);

        if ($message->status !== MessageStatus::Prepared) {
            throw ValidationException::withMessages(['message' => __('communications::messages.not_prepared')]);
        }

        $this->dispatcher->assertCanDeliver($message->channel->code, $message->recipient);
        $message->update(['user_id' => $context->actor->id]);
        $this->dispatcher->deliver($message);

        if ($message->vessel_id) {
            $context->publish(
                vessel: $message->vessel_id,
                module: 'communications',
                type: "message.{$message->status->value}",
                subject: $message,
                summaryKey: "communications::events.message_{$message->status->value}",
                params: ['template' => $message->template?->code ?? '', 'recipient' => $message->recipient],
            );
        }

        return $message;
    }
}
