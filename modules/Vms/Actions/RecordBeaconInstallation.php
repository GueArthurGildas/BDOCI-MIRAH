<?php

namespace Modules\Vms\Actions;

use App\Enums\BeaconRequestStatus;
use App\Models\Beacon;
use App\Models\BeaconInstallation;
use App\Models\NewBeaconRequest;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Vms\BeaconAssigner;
use Illuminate\Support\Carbon;

class RecordBeaconInstallation extends Action
{
    public function __construct(private readonly BeaconAssigner $assigner) {}

    public function code(): string
    {
        return 'vms.record_installation';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): BeaconInstallation
    {
        $request = NewBeaconRequest::findOrFail($input['new_beacon_request_id']);
        $installedOn = isset($input['installed_on']) ? Carbon::parse($input['installed_on']) : today();

        $installation = BeaconInstallation::create([
            'new_beacon_request_id' => $request->id,
            'installer_id' => $input['installer_id'],
            'installed_on' => $installedOn,
        ]);

        if (isset($input['beacon_id'])) {
            $this->assigner->assign($request->vessel_id, Beacon::findOrFail($input['beacon_id']), $installedOn, $input['replacement_reason'] ?? null);
        }

        $request->update(['status' => BeaconRequestStatus::Installed]);

        $context->publish(vessel: $request->vessel_id, module: 'vms', type: 'beacon.installed', subject: $installation, summaryKey: 'vms::events.beacon_installed');

        return $installation;
    }
}
