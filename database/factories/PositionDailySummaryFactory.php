<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionDailySummaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'day' => today()->subDay(),
            'positions_count' => fake()->numberBetween(6, 24),
            'emission_hours' => fake()->randomFloat(2, 6, 24),
            'distance_nautical_miles' => fake()->randomFloat(2, 0, 180),
            'average_speed' => fake()->randomFloat(2, 0, 10),
            'zones' => [],
        ];
    }
}
