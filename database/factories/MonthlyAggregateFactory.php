<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyAggregateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'year_month' => now()->format('Y-m'),
            'vessel_id' => Vessel::factory(),
            'emission_rate' => fake()->randomFloat(2, 70, 100),
            'silences_count' => fake()->numberBetween(0, 5),
        ];
    }
}
