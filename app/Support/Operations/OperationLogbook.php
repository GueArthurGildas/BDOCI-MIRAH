<?php

namespace App\Support\Operations;

use App\Enums\LogbookDirection;
use App\Enums\OperationStatus;
use App\Models\Operation;
use App\Models\OperationEvent;
use App\Support\Actions\ActionContext;
use App\Support\Logbook\LogbookChannels;
use Illuminate\Validation\ValidationException;

final class OperationLogbook
{
    public function __construct(private readonly LogbookChannels $channels) {}

    /** @param array<string, mixed> $input */
    public function add(ActionContext $context, array $input, string $errorKey = 'body'): OperationEvent
    {
        $operation = Operation::findOrFail($input['operation_id']);

        if ($operation->isFinished()) {
            throw ValidationException::withMessages([$errorKey => __('operations::operations.errors.closed')]);
        }

        if ($operation->status === OperationStatus::Open) {
            $operation->update(['status' => OperationStatus::InProgress]);
        }

        return $operation->events()->create([
            'occurred_at' => $input['occurred_at'] ?? now(),
            'channel_id' => $input['channel_id'] ?? $this->channels->idFor(LogbookChannels::INTERNAL),
            'direction' => $input['direction'] ?? LogbookDirection::Received,
            'body' => $input['body'],
            'is_order_or_decision' => (bool) ($input['is_order_or_decision'] ?? false),
            'entered_by' => $context->actor->id,
        ]);
    }
}
