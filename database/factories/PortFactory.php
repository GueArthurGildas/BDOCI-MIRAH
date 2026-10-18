<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PortFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'locality' => fake()->city(),
            'latitude' => fake()->randomFloat(6, 4.3, 5.3),
            'longitude' => fake()->randomFloat(6, -7.4, -3.0),
        ];
    }
}
