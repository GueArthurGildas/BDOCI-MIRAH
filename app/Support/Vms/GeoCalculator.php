<?php

namespace App\Support\Vms;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class GeoCalculator
{
    public const METERS_PER_NAUTICAL_MILE = 1852;

    private const POINT = 'ST_SetSRID(ST_MakePoint(longitude::double precision, latitude::double precision), 4326)';

    /** @param array<string, mixed> $coastline GeoJSON geometry */
    public function distanceToCoastNauticalMiles(float $latitude, float $longitude, array $coastline): float
    {
        $row = DB::selectOne(
            'SELECT ST_Distance(ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)::geography) / ? AS nm',
            [$longitude, $latitude, json_encode($coastline), self::METERS_PER_NAUTICAL_MILE],
        );

        return round((float) $row->nm, 2);
    }

    /** @return Collection<int, int> */
    public function zonesTraversed(int $vesselId, Carbon $from, Carbon $to): Collection
    {
        return collect(DB::select(
            'SELECT DISTINCT z.id FROM zones z, ('.$this->segmentsSql().' UNION ALL SELECT '.self::POINT.' AS g FROM positions WHERE vessel_id = ? AND recorded_at >= ? AND recorded_at < ?) s
             WHERE z.is_active AND z.geom IS NOT NULL AND ST_Intersects(z.geom, s.g)',
            [$vesselId, $from, $to, $vesselId, $from, $to],
        ))->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
    }

    /** @return Collection<int, float> nautical miles keyed by zone id */
    public function nauticalMilesPerZone(int $vesselId, Carbon $from, Carbon $to): Collection
    {
        return collect(DB::select(
            'SELECT z.id, SUM(CASE WHEN ST_Covers(z.geom, s.g) THEN ST_Length(s.g::geography) ELSE ST_Length(ST_Intersection(s.g, z.geom)::geography) END) / ? AS nm FROM zones z JOIN ('.$this->segmentsSql().') s ON ST_Intersects(z.geom, s.g)
             WHERE z.is_active AND z.geom IS NOT NULL GROUP BY z.id ORDER BY z.id',
            [self::METERS_PER_NAUTICAL_MILE, $vesselId, $from, $to],
        ))->mapWithKeys(fn ($row) => [(int) $row->id => round((float) $row->nm, 2)]);
    }

    public function distanceNauticalMiles(int $vesselId, Carbon $from, Carbon $to): float
    {
        $row = DB::selectOne(
            'SELECT COALESCE(SUM(ST_Length(s.g::geography)), 0) / ? AS nm FROM ('.$this->segmentsSql().') s',
            [self::METERS_PER_NAUTICAL_MILE, $vesselId, $from, $to],
        );

        return round((float) $row->nm, 2);
    }

    private function segmentsSql(): string
    {
        return 'SELECT ST_MakeLine(t.previous, t.p) AS g FROM (
                    SELECT '.self::POINT.' AS p, LAG('.self::POINT.') OVER (ORDER BY recorded_at) AS previous
                    FROM positions WHERE vessel_id = ? AND recorded_at >= ? AND recorded_at < ?
                ) t WHERE t.previous IS NOT NULL';
    }
}
