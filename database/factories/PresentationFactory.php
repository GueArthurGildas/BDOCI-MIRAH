<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PresentationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'label' => fake()->words(2, true),
            'conversion_factor' => fake()->randomFloat(3, 1, 3),
        ];
    }
}
