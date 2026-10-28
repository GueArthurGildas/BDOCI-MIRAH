<?php

namespace App\Support\Vms;

use App\Models\BeaconAssignment;
use App\Models\Vessel;
use App\Models\VesselReportingObligation;
use Illuminate\Support\Collection;

final class BeaconCompliance
{
    /** @return Collection<int, Vessel> */
    public function vesselsWithoutBeacon(): Collection
    {
        $equipped = BeaconAssignment::active()->pluck('vessel_id');

        return Vessel::whereIn('id', VesselReportingObligation::where('requires_vms', true)->pluck('vessel_id'))
            ->whereNotIn('id', $equipped)
            ->orderBy('name')
            ->get();
    }
}
