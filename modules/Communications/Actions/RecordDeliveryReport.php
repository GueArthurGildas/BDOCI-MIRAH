<?php

namespace Modules\Communications\Actions;

use App\Enums\MessageStatus;
use App\Models\SentMessage;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RecordDeliveryReport extends Action
{
    public function code(): string
    {
        return 'communications.record_delivery_report';
    }

    public function permission(): Permission
    {
        return Permission::CommunicationsManageDispatch;
    }

    public function handle(ActionContext $context, array $input): SentMessage
    {
        $message = SentMessage::lockForUpdate()->findOrFail($input['message_id']);
        $status = MessageStatus::from($input['event']);

        if ($message->status !== MessageStatus::Sent) {
            return $message;
        }

        $message->update($status === MessageStatus::Acknowledged
            ? ['status' => $status->value, 'acknowledged_at' => now(), 'reply_received' => (bool) ($input['reply_received'] ?? false)]
            : ['status' => $status->value]);

        if ($message->vessel_id) {
            $context->publish(
                vessel: $message->vessel_id,
                module: 'communications',
                type: "message.{$status->value}",
                subject: $message,
                summaryKey: "communications::events.message_{$status->value}",
                params: ['template' => $message->template?->code ?? '', 'recipient' => $message->recipient],
            );
        }

        return $message;
    }
}
