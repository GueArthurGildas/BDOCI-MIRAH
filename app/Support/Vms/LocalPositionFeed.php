<?php

namespace App\Support\Vms;

use App\Enums\PositionLoadingSource;
use App\Models\Position;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class LocalPositionFeed implements PositionFeed
{
    public function source(): string
    {
        return PositionLoadingSource::Api->value;
    }

    public function snapshot(Carbon $at): Collection
    {
        $latest = Position::latestPerVessel($at)->get()->keyBy('vessel_id');

        return MonitoredVessels::ids()->map(function (int $vesselId) use ($latest) {
            $position = $latest->get($vesselId);

            return new PositionSnapshot(
                $vesselId,
                $position?->latitude,
                $position?->longitude,
                $position?->speed_knots,
                $position?->zone_id,
                $position?->recorded_at,
                $position?->activity,
            );
        });
    }
}
