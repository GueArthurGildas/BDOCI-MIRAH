<?php

namespace Modules\Reports\Actions;

use App\Enums\OperationStatus;
use App\Models\Operation;
use App\Models\OperationArchive;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;
use Modules\Reports\Support\ArchivableOperations;

class ArchiveOperation extends Action
{
    public function __construct(private readonly ArchivableOperations $archivable) {}

    public function code(): string
    {
        return 'reports.archive_operation';
    }

    public function permission(): Permission
    {
        return Permission::ReportsArchive;
    }

    public function handle(ActionContext $context, array $input): OperationArchive
    {
        $operation = Operation::lockForUpdate()->findOrFail($input['operation_id']);

        if (! $this->archivable->isEligible($operation)) {
            throw ValidationException::withMessages(['operation' => __('reports::reports.archives.errors.not_eligible')]);
        }

        $operation->update(['status' => OperationStatus::Archived]);

        $archive = OperationArchive::create(['operation_id' => $operation->id, 'archived_at' => now()]);

        foreach ($operation->vessels as $vessel) {
            $context->publish(
                vessel: $vessel,
                module: 'reports',
                type: 'operation.archived',
                subject: $operation,
                summaryKey: 'reports::events.operation_archived',
                params: ['code' => $operation->code],
            );
        }

        return $archive;
    }
}
