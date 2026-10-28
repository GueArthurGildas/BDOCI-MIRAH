<?php

namespace Modules\Operations\Actions;

use App\Enums\OperationStatus;
use App\Models\Operation;
use App\Models\OperationEvent;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class AddOperationEvent extends Action
{
    public function code(): string
    {
        return 'operations.add_event';
    }

    public function permission(): Permission
    {
        return Permission::OperationsCreate;
    }

    public function handle(ActionContext $context, array $input): OperationEvent
    {
        $operation = Operation::findOrFail($input['operation_id']);

        if (in_array($operation->status, [OperationStatus::Closed, OperationStatus::Archived], true)) {
            throw ValidationException::withMessages(['body' => __('operations::operations.errors.closed')]);
        }

        if ($operation->status === OperationStatus::Open) {
            $operation->update(['status' => OperationStatus::InProgress]);
        }

        return $operation->events()->create([
            'occurred_at' => $input['occurred_at'] ?? now(),
            'body' => $input['body'],
        ]);
    }
}
