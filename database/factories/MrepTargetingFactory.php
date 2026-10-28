<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MrepTargetingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'year' => 2026,
            'objective_pct' => 20,
            'achieved_pct' => fake()->randomFloat(2, 5, 25),
            'target' => fake()->unique()->randomElement(['foreign_fishing', 'reefer', 'transshipment', 'high_risk_flag']),
        ];
    }
}
