<?php

namespace Tests\Concerns;

use App\Enums\ZoneType;
use App\Models\Zone;

trait CreatesZones
{
    protected function squareZone(string $name, ZoneType $type, float $latitude, float $longitude, float $radius): Zone
    {
        return Zone::create([
            'name' => $name,
            'type' => $type,
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [$longitude - $radius, $latitude - $radius],
                    [$longitude + $radius, $latitude - $radius],
                    [$longitude + $radius, $latitude + $radius],
                    [$longitude - $radius, $latitude + $radius],
                    [$longitude - $radius, $latitude - $radius],
                ]],
            ],
        ]);
    }
}
