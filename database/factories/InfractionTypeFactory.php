<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InfractionTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('INF-##'),
            'label' => fake()->sentence(3),
            'is_active' => true,
        ];
    }
}
