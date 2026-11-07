<?php

namespace App\Queries;

use App\Models\Position;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class ZoneSituation
{
    public const POSITION_FRESHNESS_HOURS = 24;

    /** @return Collection<int, int> distinct vessels seen in each zone during the period, keyed by zone id */
    public static function vesselCountByZoneBetween(Carbon $from, Carbon $to): Collection
    {
        return Position::query()
            ->whereBetween('recorded_at', [$from, $to])
            ->whereNotNull('zone_id')
            ->selectRaw('zone_id, count(distinct vessel_id) as total')
            ->groupBy('zone_id')
            ->pluck('total', 'zone_id')
            ->map(fn ($total) => (int) $total);
    }

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
