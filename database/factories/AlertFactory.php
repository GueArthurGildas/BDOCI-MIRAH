<?php

namespace Database\Factories;

use App\Enums\AlertRuleType;
use App\Enums\AlertStatus;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'alert_rule_id' => null,
            'type' => AlertRuleType::Silence,
            'triggered_at' => now()->subHour(),
            'status' => AlertStatus::Open,
        ];
    }
}
