<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class RotationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'day' => today()->addDays(fake()->unique()->numberBetween(1, 3000))->toDateString(),
        ];
    }
}
