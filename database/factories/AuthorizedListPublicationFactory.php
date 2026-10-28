<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorizedListPublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version' => fake()->unique()->numberBetween(1, 100000),
            'published_at' => now(),
        ];
    }
}
