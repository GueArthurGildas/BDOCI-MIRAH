<?php

namespace App\Support\Vms;

use App\Models\BeaconAssignment;
use App\Models\TrackingDevice;
use Illuminate\Support\Collection;

final class MonitoredVessels
{
    /** @return Collection<int, int> */
    public static function ids(): Collection
    {
        return TrackingDevice::whereNull('removed_at')->pluck('vessel_id')
            ->merge(BeaconAssignment::active()->pluck('vessel_id'))
            ->unique()
            ->values();
    }
}
