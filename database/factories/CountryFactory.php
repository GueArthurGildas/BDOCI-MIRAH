<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'iso3_code' => strtoupper(fake()->unique()->lexify('???')),
            'iso2_code' => strtoupper(fake()->unique()->lexify('??')),
            'label' => fake()->country(),
        ];
    }
}
