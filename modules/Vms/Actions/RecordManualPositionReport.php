<?php

namespace Modules\Vms\Actions;

use App\Models\ManualPositionReport;
use App\Models\VmsFailure;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class RecordManualPositionReport extends Action
{
    public function code(): string
    {
        return 'vms.record_manual_report';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): ManualPositionReport
    {
        $failure = VmsFailure::findOrFail($input['vms_failure_id']);

        $report = ManualPositionReport::create([
            'vms_failure_id' => $failure->id,
            'received_at' => isset($input['received_at']) ? Carbon::parse($input['received_at']) : now(),
            'latitude' => $input['latitude'],
            'longitude' => $input['longitude'],
            'entered_by' => $context->actor->id,
        ]);

        $context->publish(vessel: $failure->vessel_id, module: 'vms', type: 'failure.manual_report', subject: $report, summaryKey: 'vms::events.manual_report_recorded');

        return $report;
    }
}
