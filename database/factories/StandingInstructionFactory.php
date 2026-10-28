<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class StandingInstructionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'body' => fake()->sentence(),
            'started_on' => today()->subDays(fake()->numberBetween(0, 30)),
        ];
    }
}
