<?php

namespace Modules\Witnesses\Actions;

use App\Models\WitnessReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\RecordDeletion;

class DeleteWitnessReport extends Action
{
    public function __construct(private readonly RecordDeletion $deletion) {}

    public function code(): string
    {
        return 'witnesses.delete_report';
    }

    public function permission(): Permission
    {
        return Permission::WitnessesDelete;
    }

    public function handle(ActionContext $context, array $input): WitnessReport
    {
        $report = WitnessReport::with(['logbookEntry.day', 'reportedVessels'])->findOrFail($input['report_id']);
        $this->deletion->ensureDeletable($report->logbookEntry);

        $this->deletion->cancel($report->logbookEntry, $context->actor, $input['reason']);
        $this->deletion->delete($report, $context->actor, $input['reason']);

        $report->reportedVessels->pluck('vessel_id')->filter()->unique()->each(fn (int $vesselId) => $context->publish(
            vessel: $vesselId,
            module: 'witnesses',
            type: 'report.deleted',
            subject: $report,
            summaryKey: 'witnesses::events.report_deleted',
            params: ['id' => $report->id],
        ));

        return $report;
    }
}
