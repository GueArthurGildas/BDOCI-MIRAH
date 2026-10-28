<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MrepGiesRefusalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'reference' => fake()->unique()->numerify('GIES-2026-####'),
            'refused_on' => today()->subDays(fake()->numberBetween(1, 90)),
        ];
    }
}
