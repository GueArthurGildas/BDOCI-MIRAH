<?php

namespace App\Queries;

use App\Enums\MissionStatus;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class VesselMissions
{
    public static function ongoing(): Builder
    {
        return Mission::query()->where('status', MissionStatus::InProgress->value);
    }

    /** @return Collection<int, Mission> */
    public static function forVessel(int $vesselId): Collection
    {
        return Mission::query()->whereHas('observedUnits', fn (Builder $units) => $units->where('vessel_id', $vesselId))->latest('planned_start_at')->get();
    }
}
