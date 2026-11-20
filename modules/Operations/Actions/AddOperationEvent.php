<?php

namespace Modules\Operations\Actions;

use App\Models\OperationEvent;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Operations\OperationLogbook;

class AddOperationEvent extends Action
{
    public function __construct(private readonly OperationLogbook $logbook) {}

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
        return $this->logbook->add($context, $input);
    }
}
