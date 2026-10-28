<?php

namespace App\Support\Vms;

use App\Models\Track;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class TrackBuilder
{
    public function build(int $vesselId, Carbon $from, Carbon $to): ?Track
    {
        $line = DB::selectOne(
            'SELECT ST_AsEWKT(ST_MakeLine(ST_SetSRID(ST_MakePoint(longitude::double precision, latitude::double precision), 4326) ORDER BY recorded_at)) AS line, COUNT(*) AS points
             FROM positions WHERE vessel_id = ? AND recorded_at >= ? AND recorded_at < ?',
            [$vesselId, $from, $to],
        );

        if ($line->points < 2) {
            return null;
        }

        $track = Track::create(['vessel_id' => $vesselId, 'started_at' => $from, 'ended_at' => $to]);

        DB::update('UPDATE tracks SET geometry = ST_GeomFromEWKT(?) WHERE id = ?', [$line->line, $track->id]);

        return $track;
    }
}
