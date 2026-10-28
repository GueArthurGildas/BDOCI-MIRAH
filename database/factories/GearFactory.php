<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GearFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code_fao' => strtoupper(fake()->unique()->lexify('???')),
            'label' => fake()->words(2, true),
        ];
    }
}
