<?php

namespace Modules\Witnesses\Actions;

use App\Enums\WitnessReportStatus;
use App\Models\ReportUsability;
use App\Models\WitnessReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class SaveReportUsability extends Action
{
    public function code(): string
    {
        return 'witnesses.save_usability';
    }

    public function permission(): Permission
    {
        return Permission::WitnessesManage;
    }

    public function handle(ActionContext $context, array $input): ReportUsability
    {
        $report = WitnessReport::findOrFail($input['witness_report_id']);

        $usability = $report->usability()->updateOrCreate([], [
            'is_usable' => (bool) $input['is_usable'],
            'interview_report' => $input['interview_report'] ?? null,
        ]);

        if ($usability->is_usable && $report->status === WitnessReportStatus::Received) {
            $report->update(['status' => WitnessReportStatus::Verified]);
        }

        return $usability;
    }
}
