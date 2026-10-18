<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SpeciesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fao_code' => fake()->unique()->lexify('???'),
            'name' => fake()->word(),
            'scientific_name' => fake()->words(2, true),
        ];
    }
}
