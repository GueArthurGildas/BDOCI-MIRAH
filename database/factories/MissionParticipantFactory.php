<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissionParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'agent_id' => Agent::factory(),
            'is_mission_lead' => false,
        ];
    }
}
