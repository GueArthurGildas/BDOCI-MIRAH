<?php

namespace Modules\Logbook\Actions;

use App\Models\OperationEvent;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Operations\OperationLogbook;

class RecordOperationLogbookLine extends Action
{
    public function __construct(private readonly OperationLogbook $logbook) {}

    public function code(): string
    {
        return 'logbook.record_operation_line';
    }

    public function permission(): Permission
    {
        return Permission::LogbookCreate;
    }

    public function handle(ActionContext $context, array $input): OperationEvent
    {
        return $this->logbook->add($context, $input, 'operation_id');
    }
}
