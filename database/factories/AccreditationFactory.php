<?php

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccreditationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agent_id' => Agent::factory(),
            'card_number' => fake()->unique()->numerify('CSP-####'),
            'decision' => fake()->sentence(3),
        ];
    }
}
