<?php

namespace Modules\Witnesses\Actions;

use App\Models\ReportInvestigation;
use App\Models\WitnessReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class SaveReportInvestigation extends Action
{
    public function code(): string
    {
        return 'witnesses.save_investigation';
    }

    public function permission(): Permission
    {
        return Permission::WitnessesManage;
    }

    public function handle(ActionContext $context, array $input): ReportInvestigation
    {
        $report = WitnessReport::findOrFail($input['witness_report_id']);

        return $report->investigation()->updateOrCreate([], [
            'elements' => array_values(array_unique($input['elements'] ?? [])),
            'notes' => $input['notes'] ?? null,
        ]);
    }
}
