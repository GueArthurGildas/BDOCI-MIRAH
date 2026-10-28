<?php

namespace Modules\Operations\Actions;

use App\Enums\OperationStatus;
use App\Models\Operation;
use App\Models\OperationInstruction;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class GiveOperationInstruction extends Action
{
    public function code(): string
    {
        return 'operations.give_instruction';
    }

    public function permission(): Permission
    {
        return Permission::OperationsManage;
    }

    public function handle(ActionContext $context, array $input): OperationInstruction
    {
        $operation = Operation::findOrFail($input['operation_id']);

        if (in_array($operation->status, [OperationStatus::Closed, OperationStatus::Archived], true)) {
            throw ValidationException::withMessages(['body' => __('operations::operations.errors.closed')]);
        }

        return $operation->instructions()->create(['body' => $input['body'], 'given_by' => $context->actor->id]);
    }
}
