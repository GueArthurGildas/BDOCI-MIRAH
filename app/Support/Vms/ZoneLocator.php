<?php

namespace App\Support\Vms;

use Illuminate\Support\Facades\DB;

final class ZoneLocator
{
    public function zoneIdFor(float $latitude, float $longitude): ?int
    {
        $row = DB::selectOne(
            "SELECT id FROM zones WHERE is_active AND geom IS NOT NULL AND ST_Covers(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))
             ORDER BY CASE type WHEN 'port_area' THEN 0 WHEN 'restricted' THEN 1 WHEN 'territorial_sea' THEN 2 ELSE 3 END, id LIMIT 1",
            [$longitude, $latitude],
        );

        return $row?->id;
    }
}
