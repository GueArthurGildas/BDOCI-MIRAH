<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ControlSlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'started_at' => now()->startOfHour(),
            'ended_at' => now()->startOfHour()->addHours(2),
            'team_id' => null,
            'operator_user_id' => null,
        ];
    }
}
