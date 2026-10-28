<?php

namespace Modules\Operations\Actions;

use App\Enums\OperationStatus;
use App\Models\Operation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateOperation extends Action
{
    public function code(): string
    {
        return 'operations.update';
    }

    public function permission(): Permission
    {
        return Permission::OperationsManage;
    }

    public function handle(ActionContext $context, array $input): Operation
    {
        $operation = Operation::findOrFail($input['operation_id']);

        if (in_array($operation->status, [OperationStatus::Closed, OperationStatus::Archived], true)) {
            throw ValidationException::withMessages(['summary' => __('operations::operations.errors.closed')]);
        }

        $operation->update(Arr::only($input, ['status', 'summary', 'origin']));

        return $operation;
    }
}
