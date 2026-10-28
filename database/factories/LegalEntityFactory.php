<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LegalEntityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'address' => fake()->address(),
            'phone' => fake()->numerify('+225 ## ## ## ##'),
            'email' => fake()->companyEmail(),
        ];
    }
}
