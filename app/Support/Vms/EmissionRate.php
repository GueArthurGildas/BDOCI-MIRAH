<?php

namespace App\Support\Vms;

use App\Models\EmissionCutoff;
use App\Models\Vessel;
use App\Models\VmsCutoffAuthorization;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class EmissionRate
{
    private const NATIONAL_FLAG = 'CI';

    /** @return array{required_hours: int, emitted_hours: int, rate: ?float} */
    public function forVessel(Vessel $vessel, Carbon $from, Carbon $to): array
    {
        $window = $this->requiredWindow($vessel, $from, $to);

        if ($window === null) {
            return ['required_hours' => 0, 'emitted_hours' => 0, 'rate' => null];
        }

        [$start, $end] = $window;
        $excluded = $this->authorizedCutoffHours($vessel, $start, $end);
        $required = max((int) round($start->diffInHours($end)) - $excluded, 0);

        $emitted = (int) DB::selectOne(
            "SELECT COUNT(DISTINCT date_trunc('hour', recorded_at)) AS hours FROM positions WHERE vessel_id = ? AND recorded_at >= ? AND recorded_at < ?",
            [$vessel->id, $start, $end],
        )->hours;

        return [
            'required_hours' => $required,
            'emitted_hours' => $emitted,
            'rate' => $required === 0 ? null : round(min($emitted / $required * 100, 100), 2),
        ];
    }

    /** @return array{0: Carbon, 1: Carbon}|null */
    private function requiredWindow(Vessel $vessel, Carbon $from, Carbon $to): ?array
    {
        if ($vessel->flag === self::NATIONAL_FLAG) {
            return [$from->copy(), $to->copy()];
        }

        $bounds = DB::selectOne(
            "SELECT MIN(p.recorded_at) AS first, MAX(p.recorded_at) AS last
             FROM positions p JOIN zones z ON z.type = 'exclusive_economic_zone' AND z.geom IS NOT NULL
                AND ST_Covers(z.geom, ST_SetSRID(ST_MakePoint(p.longitude::double precision, p.latitude::double precision), 4326))
             WHERE p.vessel_id = ? AND p.recorded_at >= ? AND p.recorded_at < ?",
            [$vessel->id, $from, $to],
        );

        if ($bounds->first === null) {
            return null;
        }

        return [Carbon::parse($bounds->first)->startOfHour(), Carbon::parse($bounds->last)->startOfHour()->addHour()];
    }

    private function authorizedCutoffHours(Vessel $vessel, Carbon $start, Carbon $end): int
    {
        $periods = VmsCutoffAuthorization::where('vessel_id', $vessel->id)->where('started_at', '<', $end)->where('ended_at', '>', $start)
            ->get(['started_at', 'ended_at'])
            ->map(fn ($cutoff) => [$cutoff->started_at, $cutoff->ended_at])
            ->concat(EmissionCutoff::where('vessel_id', $vessel->id)->where('is_technical_stop', true)->where('started_at', '<', $end)
                ->where(fn ($query) => $query->whereNull('ended_at')->orWhere('ended_at', '>', $start))
                ->get(['started_at', 'ended_at'])
                ->map(fn ($cutoff) => [$cutoff->started_at, $cutoff->ended_at ?? $end]));

        return (int) $periods->sum(fn (array $period) => max((int) round($period[0]->max($start)->diffInHours($period[1]->min($end))), 0));
    }
}
