<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'grade' => fake()->randomElement(['Inspector', 'Officer']),
            'agency' => 'CSP',
            'is_inspector' => false,
        ];
    }
}
