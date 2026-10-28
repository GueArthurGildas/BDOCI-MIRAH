<?php

namespace Database\Factories;

use App\Models\InfractionCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeizureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'infraction_case_id' => InfractionCase::factory(),
            'item' => fake()->randomElement(['Palangre', 'Filet maillant', 'Captures']),
        ];
    }
}
