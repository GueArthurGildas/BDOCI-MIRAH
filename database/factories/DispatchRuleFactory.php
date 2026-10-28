<?php

namespace Database\Factories;

use App\Enums\DispatchMode;
use App\Models\MessageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class DispatchRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'message_template_id' => MessageTemplate::factory(),
            'trigger_event' => 'beacon_silent',
            'conditions' => ['recipient' => 'armement@example.ci'],
            'frequency_minutes' => 120,
            'daily_cap' => 3,
            'stop_condition' => 'acknowledged',
            'mode' => DispatchMode::Automatic,
            'is_active' => true,
            'editable_by_permission_group_id' => null,
        ];
    }
}
