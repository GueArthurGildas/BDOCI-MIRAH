<?php

namespace App\Queries;

use App\Enums\BeaconStatus;
use App\Models\Position;
use App\Models\TrackingDevice;
use App\Support\Vms\SilenceThreshold;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BeaconStates
{
    public static function query(): Builder
    {
        $latestPosition = Position::query()
            ->select('recorded_at', 'zone_id')
            ->whereColumn('positions.vessel_id', 'tracking_devices.vessel_id')
            ->orderByDesc('recorded_at')
            ->limit(1);

        return TrackingDevice::query()
            ->whereNull('tracking_devices.removed_at')
            ->leftJoinLateral($latestPosition, 'last_position')
            ->select('tracking_devices.*')
            ->selectRaw('last_position.recorded_at as last_emission_at, last_position.zone_id as last_zone_id')
            ->selectRaw(self::stateSql().' as beacon_state', [self::cutoff()]);
    }

    public static function whereState(Builder $query, BeaconStatus $status): Builder
    {
        return $query->whereRaw(self::stateSql().' = ?', [self::cutoff(), $status->value]);
    }

    public static function whereSilentFor(Builder $query, int $hours): Builder
    {
        return $query->where('last_position.recorded_at', '<=', now()->subHours($hours));
    }

    public static function orderByUrgency(Builder $query): Builder
    {
        return $query
            ->orderByRaw('case when last_position.recorded_at is null then 1 when last_position.recorded_at < ? then 0 else 2 end', [self::cutoff()])
            ->orderBy('last_position.recorded_at')
            ->orderBy('tracking_devices.id');
    }

    /** @return Collection<string, int> */
    public static function countByState(Builder $query): Collection
    {
        return DB::query()
            ->fromSub($query->clone()->reorder(), 'states')
            ->selectRaw('beacon_state, count(*) as total')
            ->groupBy('beacon_state')
            ->pluck('total', 'beacon_state');
    }

    public static function cutoff(): Carbon
    {
        return now()->subMinutes((int) round(app(SilenceThreshold::class)->hours() * 60));
    }

    private static function stateSql(): string
    {
        return sprintf(
            "case when last_position.recorded_at is null then '%s' when last_position.recorded_at < ? then '%s' else '%s' end",
            BeaconStatus::NoData->value,
            BeaconStatus::Silent->value,
            BeaconStatus::Emitting->value,
        );
    }
}
