<?php

namespace Modules\Vms\Actions;

use App\Models\Beacon;
use App\Models\BeaconAssignment;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Vms\BeaconAssigner;
use Illuminate\Support\Carbon;

class AssignBeacon extends Action
{
    public function __construct(private readonly BeaconAssigner $assigner) {}

    public function code(): string
    {
        return 'vms.assign_beacon';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): BeaconAssignment
    {
        $assignment = $this->assigner->assign(
            $input['vessel_id'],
            Beacon::findOrFail($input['beacon_id']),
            isset($input['started_on']) ? Carbon::parse($input['started_on']) : today(),
            $input['replacement_reason'] ?? null,
        );

        $context->publish(
            vessel: $assignment->vessel_id,
            module: 'vms',
            type: 'beacon.assigned',
            subject: $assignment,
            summaryKey: 'vms::events.beacon_assigned',
            params: ['serial' => $assignment->beacon->serial_number],
        );

        return $assignment;
    }
}
