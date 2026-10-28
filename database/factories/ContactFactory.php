<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(['Administration', 'Navy', 'Customs']),
            'organization' => fake()->company(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('+225 ## ## ## ##'),
        ];
    }
}
