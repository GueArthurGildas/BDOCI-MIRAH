<?php

namespace Modules\Operations\Actions;

use App\Enums\OperationStatus;
use App\Models\Operation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Watch\WatchList;
use Illuminate\Validation\ValidationException;

class CloseOperation extends Action
{
    public function __construct(private readonly WatchList $watchList) {}

    public function code(): string
    {
        return 'operations.close';
    }

    public function permission(): Permission
    {
        return Permission::OperationsManage;
    }

    public function handle(ActionContext $context, array $input): Operation
    {
        $operation = Operation::findOrFail($input['operation_id']);

        if (in_array($operation->status, [OperationStatus::Closed, OperationStatus::Archived], true)) {
            throw ValidationException::withMessages(['result' => __('operations::operations.errors.closed')]);
        }

        $operation->update([
            'status' => OperationStatus::Closed,
            'result' => $input['result'],
            'ended_at' => now(),
            'closed_by' => $context->actor->id,
        ]);

        $this->watchList->closeForOperation($operation);

        foreach ($operation->vessels as $vessel) {
            $context->publish(
                vessel: $vessel,
                module: 'operations',
                type: 'operation.closed',
                subject: $operation,
                summaryKey: 'operations::events.closed',
                params: ['code' => $operation->code],
            );
        }

        return $operation;
    }
}
