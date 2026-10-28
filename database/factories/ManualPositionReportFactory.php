<?php

namespace Database\Factories;

use App\Models\VmsFailure;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManualPositionReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vms_failure_id' => VmsFailure::factory(),
            'received_at' => now(),
            'latitude' => fake()->randomFloat(6, 4.0, 5.0),
            'longitude' => fake()->randomFloat(6, -7.0, -4.0),
        ];
    }
}
