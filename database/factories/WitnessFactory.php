<?php

namespace Database\Factories;

use App\Enums\WitnessCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class WitnessFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'category' => WitnessCategory::Fisher,
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+225 07 ## ## ## ##'),
        ];
    }
}
