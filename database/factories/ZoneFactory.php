<?php

namespace Database\Factories;

use App\Enums\ZoneType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    public function definition(): array
    {
        $latitude = fake()->randomFloat(2, 4.3, 5.2);
        $longitude = fake()->randomFloat(2, -7.4, -3.0);

        return [
            'name' => fake()->unique()->city(),
            'type' => ZoneType::PortArea,
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [$longitude - 0.1, $latitude - 0.1],
                    [$longitude + 0.1, $latitude - 0.1],
                    [$longitude + 0.1, $latitude + 0.1],
                    [$longitude - 0.1, $latitude + 0.1],
                    [$longitude - 0.1, $latitude - 0.1],
                ]],
            ],
            'is_active' => true,
        ];
    }
}
