<?php

namespace Database\Factories;

use App\Enums\TrackingSystem;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'system' => TrackingSystem::Vms,
            'recorded_at' => now()->subMinutes(fake()->numberBetween(5, 60)),
            'latitude' => fake()->randomFloat(6, 3.5, 5.0),
            'longitude' => fake()->randomFloat(6, -7.4, -3.0),
            'speed_knots' => fake()->randomFloat(2, 0, 12),
            'course' => fake()->numberBetween(0, 359),
        ];
    }
}
