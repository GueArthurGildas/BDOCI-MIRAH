<?php

namespace App\Queries;

use App\Models\Position;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class ZoneSituation
{
    public const POSITION_FRESHNESS_HOURS = 24;

    /** @return Collection<int, int> vessel count keyed by zone id */
    public static function vesselCountByZoneAt(Carbon $at): Collection
    {
        return Position::latestPerVessel($at)
            ->where('positions.recorded_at', '>=', $at->copy()->subHours(self::POSITION_FRESHNESS_HOURS))
            ->whereNotNull('zone_id')
            ->get()
            ->countBy('zone_id');
    }
}
