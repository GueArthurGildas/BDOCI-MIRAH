<?php

namespace Modules\Witnesses\Actions;

use App\Enums\ReportDecisionType;
use App\Enums\WitnessReportStatus;
use App\Models\ReportDecision;
use App\Models\WitnessReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class DecideWitnessReport extends Action
{
    public function code(): string
    {
        return 'witnesses.decide';
    }

    public function permission(): Permission
    {
        return Permission::WitnessesManage;
    }

    public function handle(ActionContext $context, array $input): ReportDecision
    {
        $report = WitnessReport::lockForUpdate()->findOrFail($input['witness_report_id']);
        $decision = ReportDecisionType::from($input['decision']);

        if (in_array($report->status, [WitnessReportStatus::Closed, WitnessReportStatus::Forwarded], true)) {
            throw ValidationException::withMessages(['decision' => __('witnesses::witnesses.errors.already_decided')]);
        }

        $record = $report->decisions()->create([
            'decision' => $decision,
            'decided_by' => $context->actor->id,
            'decided_at' => now(),
        ]);

        $report->update(['status' => $decision === ReportDecisionType::Dismissal ? WitnessReportStatus::Closed : WitnessReportStatus::Forwarded]);

        foreach ($report->reportedVessels()->whereNotNull('vessel_id')->pluck('vessel_id') as $vesselId) {
            $context->publish(
                vessel: $vesselId,
                module: 'witnesses',
                type: 'witness_report.decided',
                subject: $report,
                summaryKey: 'witnesses::events.decided.'.$decision->value,
                params: ['id' => $report->id],
            );
        }

        return $record;
    }
}
