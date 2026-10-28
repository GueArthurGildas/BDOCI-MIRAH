<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VesselTypeRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'label' => fake()->words(2, true),
        ];
    }
}
