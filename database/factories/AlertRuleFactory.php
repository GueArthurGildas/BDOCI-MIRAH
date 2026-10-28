<?php

namespace Database\Factories;

use App\Enums\AlertRuleType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'type' => AlertRuleType::Silence,
            'threshold_hours' => 4,
            'parameters' => null,
            'is_active' => true,
        ];
    }
}
