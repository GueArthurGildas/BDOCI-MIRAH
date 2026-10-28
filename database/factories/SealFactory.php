<?php

namespace Database\Factories;

use App\Models\InfractionCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class SealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'infraction_case_id' => InfractionCase::factory(),
            'number' => fake()->numerify('SC-####'),
            'placed_at' => now(),
        ];
    }
}
