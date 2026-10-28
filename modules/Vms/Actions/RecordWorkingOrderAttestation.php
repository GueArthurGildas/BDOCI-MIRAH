<?php

namespace Modules\Vms\Actions;

use App\Models\VmsFailure;
use App\Models\WorkingOrderAttestation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class RecordWorkingOrderAttestation extends Action
{
    public function code(): string
    {
        return 'vms.record_attestation';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): WorkingOrderAttestation
    {
        $failure = VmsFailure::findOrFail($input['vms_failure_id']);
        $attestedOn = isset($input['attested_on']) ? Carbon::parse($input['attested_on']) : today();

        $attestation = WorkingOrderAttestation::updateOrCreate(['vms_failure_id' => $failure->id], ['attested_on' => $attestedOn]);

        $failure->update(['ended_at' => $failure->ended_at ?? $attestedOn->copy()->endOfDay()->min(now())]);

        $context->publish(vessel: $failure->vessel_id, module: 'vms', type: 'failure.repaired', subject: $attestation, summaryKey: 'vms::events.failure_repaired');

        return $attestation;
    }
}
