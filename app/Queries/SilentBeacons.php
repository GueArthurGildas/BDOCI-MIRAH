<?php

namespace App\Queries;

use App\Models\EmissionCutoff;
use App\Models\Vessel;
use App\Models\VmsCutoffAuthorization;
use App\Support\Vms\MonitoredVessels;
use Illuminate\Support\Collection;

final class SilentBeacons
{
    /** @return Collection<int, Vessel> */
    public static function vessels(?int $onlyVesselId = null): Collection
    {
        $authorized = VmsCutoffAuthorization::where('started_at', '<=', now())->where('ended_at', '>=', now())->pluck('vessel_id')
            ->merge(EmissionCutoff::where('is_technical_stop', true)->where('started_at', '<=', now())->where(fn ($query) => $query->whereNull('ended_at')->orWhere('ended_at', '>=', now()))->pluck('vessel_id'));

        return Vessel::whereIn('id', MonitoredVessels::ids())
            ->when($onlyVesselId, fn ($query, $vesselId) => $query->whereKey($vesselId))
            ->whereNotIn('id', $authorized)
            ->whereRaw('(select max(positions.recorded_at) from positions where positions.vessel_id = vessels.id) < ?', [BeaconStates::cutoff()])
            ->with('latestPosition')
            ->get();
    }
}
