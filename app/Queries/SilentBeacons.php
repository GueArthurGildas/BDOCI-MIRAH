<?php

namespace App\Queries;

use App\Enums\BeaconStatus;
use App\Models\Vessel;
use Illuminate\Support\Collection;

final class SilentBeacons
{
    /** @return Collection<int, Vessel> */
    public static function vessels(): Collection
    {
        return Vessel::whereHas('trackingDevices', fn ($query) => $query->whereNull('removed_at'))
            ->with('latestPosition')
            ->get()
            ->filter(fn (Vessel $vessel) => $vessel->beaconStatus() === BeaconStatus::Silent)
            ->values();
    }
}
