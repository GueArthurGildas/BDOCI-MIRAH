<?php

namespace Database\Factories;

use App\Enums\InstitutionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(InstitutionType::cases()),
            'name' => fake()->unique()->company(),
            'country_id' => null,
        ];
    }
}
