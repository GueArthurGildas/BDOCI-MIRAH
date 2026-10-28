<?php

namespace Modules\Communications\Actions;

use App\Enums\MessageStatus;
use App\Models\SentMessage;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class AcknowledgeMessage extends Action
{
    public function code(): string
    {
        return 'communications.acknowledge_message';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsSend;
    }

    public function handle(ActionContext $context, array $input): SentMessage
    {
        $message = SentMessage::lockForUpdate()->findOrFail($input['message_id']);

        if ($message->status !== MessageStatus::Sent) {
            throw ValidationException::withMessages(['message' => __('communications::messages.not_sent')]);
        }

        $message->update([
            'status' => MessageStatus::Acknowledged->value,
            'acknowledged_at' => now(),
            'reply_received' => (bool) ($input['reply_received'] ?? false),
        ]);

        if ($message->vessel_id) {
            $context->publish(
                vessel: $message->vessel_id,
                module: 'communications',
                type: 'message.acknowledged',
                subject: $message,
                summaryKey: 'communications::events.message_acknowledged',
                params: ['recipient' => $message->recipient],
            );
        }

        return $message;
    }
}
