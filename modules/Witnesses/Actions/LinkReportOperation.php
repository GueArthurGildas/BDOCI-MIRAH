<?php

namespace Modules\Witnesses\Actions;

use App\Enums\OperationCategoryCode;
use App\Models\Operation;
use App\Models\WitnessReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class LinkReportOperation extends Action
{
    public function code(): string
    {
        return 'witnesses.link_operation';
    }

    public function permission(): Permission
    {
        return Permission::WitnessesCreate;
    }

    public function handle(ActionContext $context, array $input): WitnessReport
    {
        $report = WitnessReport::findOrFail($input['witness_report_id']);
        $operation = Operation::with('category')->findOrFail($input['operation_id']);

        if ($operation->category->code !== OperationCategoryCode::Inn) {
            throw ValidationException::withMessages(['operation_id' => __('witnesses::witnesses.errors.operation_not_inn')]);
        }

        $report->update(['operation_id' => $operation->id]);

        return $report;
    }
}
