<?php

namespace Database\Factories;

use App\Models\Operation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationContextFactory extends Factory
{
    public function definition(): array
    {
        return [
            'operation_id' => Operation::factory(),
            'first_event' => fake()->sentence(),
            'temperature' => fake()->randomFloat(1, 24, 33),
            'pressure' => fake()->randomFloat(1, 1005, 1018),
            'visibility' => 'good',
            'cloud_cover' => 'partly_cloudy',
            'wind_beaufort' => fake()->numberBetween(1, 6),
            'sea_state' => 'moderate',
        ];
    }
}
