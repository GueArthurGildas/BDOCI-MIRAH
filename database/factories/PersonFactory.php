<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'phone' => fake()->numerify('+225 ## ## ## ##'),
            'email' => fake()->safeEmail(),
        ];
    }
}
