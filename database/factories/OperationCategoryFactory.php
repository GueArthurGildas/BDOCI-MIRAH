<?php

namespace Database\Factories;

use App\Enums\OperationCategoryCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->randomElement(OperationCategoryCode::cases()),
            'label' => fake()->words(2, true),
        ];
    }
}
