<?php

namespace Modules\Tracking\Console;

use App\Models\Position;
use App\Support\Vms\GeoCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SummarizePositions extends Command
{
    protected $signature = 'tracking:summarize-positions {--date= : Day to summarize (default: yesterday)}';

    protected $description = 'Compute the daily position summary of every vessel that emitted';

    public function handle(GeoCalculator $geo): int
    {
        $day = $this->option('date') ? Carbon::parse($this->option('date'))->startOfDay() : today()->subDay();
        $end = $day->copy()->addDay();

        Position::where('recorded_at', '>=', $day)->where('recorded_at', '<', $end)->distinct()->pluck('vessel_id')->each(function (int $vesselId) use ($geo, $day, $end) {
            $stats = DB::selectOne(
                "SELECT COUNT(*) AS positions_count, COUNT(DISTINCT date_trunc('hour', recorded_at)) AS emission_hours, AVG(speed_knots) AS average_speed
                 FROM positions WHERE vessel_id = ? AND recorded_at >= ? AND recorded_at < ?",
                [$vesselId, $day, $end],
            );

            DB::table('position_daily_summaries')->upsert([[
                'vessel_id' => $vesselId,
                'day' => $day->toDateString(),
                'positions_count' => $stats->positions_count,
                'emission_hours' => $stats->emission_hours,
                'distance_nautical_miles' => $geo->distanceNauticalMiles($vesselId, $day, $end),
                'average_speed' => $stats->average_speed === null ? null : round($stats->average_speed, 2),
                'zones' => json_encode($geo->nauticalMilesPerZone($vesselId, $day, $end)->all()),
            ]], ['vessel_id', 'day'], ['positions_count', 'emission_hours', 'distance_nautical_miles', 'average_speed', 'zones']);
        });

        return self::SUCCESS;
    }
}
