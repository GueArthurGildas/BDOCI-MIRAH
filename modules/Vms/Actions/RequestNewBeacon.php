<?php

namespace Modules\Vms\Actions;

use App\Enums\BeaconRequestStatus;
use App\Models\NewBeaconRequest;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RequestNewBeacon extends Action
{
    public function code(): string
    {
        return 'vms.request_new_beacon';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): NewBeaconRequest
    {
        $request = NewBeaconRequest::create([
            'vessel_id' => $input['vessel_id'],
            'requested_on' => $input['requested_on'] ?? today(),
            'status' => BeaconRequestStatus::Requested,
        ]);

        $context->publish(vessel: $request->vessel_id, module: 'vms', type: 'new_beacon.requested', subject: $request, summaryKey: 'vms::events.new_beacon_requested');

        return $request;
    }
}
