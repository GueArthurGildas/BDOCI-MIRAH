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
        return Mission::query()->whereHas('vessels', fn (Builder $vessels) => $vessels->whereKey($vesselId))->latest('starts_at')->get();
    }
}
