<?php

namespace App\Support\Vms;

use App\Enums\PositionLoadingSource;
use App\Enums\TrackingSystem;
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
        $emissions = Position::query()
            ->where('system', '!=', TrackingSystem::Manual->value)
            ->where('recorded_at', '<=', $at)
            ->whereIn('vessel_id', $latest->where('system', TrackingSystem::Manual)->keys())
            ->groupBy('vessel_id')
            ->selectRaw('vessel_id, max(recorded_at) as emitted_at')
            ->pluck('emitted_at', 'vessel_id');

        return MonitoredVessels::ids()->map(function (int $vesselId) use ($latest, $emissions) {
            $position = $latest->get($vesselId);
            $emittedAt = $position?->system === TrackingSystem::Manual
                ? ($emissions->has($vesselId) ? Carbon::parse($emissions->get($vesselId)) : null)
                : $position?->recorded_at;

            return new PositionSnapshot(
                $vesselId,
                $position?->latitude,
                $position?->longitude,
                $position?->speed_knots,
                $position?->zone_id,
                $emittedAt,
                $position?->activity,
            );
        });
    }
}
