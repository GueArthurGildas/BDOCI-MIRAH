<?php

namespace App\Support\Vms;

use App\Models\Beacon;
use App\Models\BeaconAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class BeaconAssigner
{
    public function __construct(private readonly BeaconMirror $mirror) {}

    public function assign(int $vesselId, Beacon $beacon, Carbon $startedOn, ?string $reason = null): BeaconAssignment
    {
        $beaconInUse = BeaconAssignment::active()->where('beacon_id', $beacon->id)->where('vessel_id', '!=', $vesselId)->exists();

        if ($beaconInUse) {
            throw ValidationException::withMessages(['beacon_id' => __('vms::vms.errors.beacon_in_use')]);
        }

        $current = BeaconAssignment::active()->where('vessel_id', $vesselId)->first();

        if ($current?->beacon_id === $beacon->id) {
            return $current;
        }

        $current?->update(['ended_on' => $startedOn]);

        $assignment = BeaconAssignment::create([
            'vessel_id' => $vesselId,
            'beacon_id' => $beacon->id,
            'started_on' => $startedOn,
            'replacement_reason' => $current ? $reason : null,
        ]);

        $this->mirror->project($vesselId);

        return $assignment;
    }
}
