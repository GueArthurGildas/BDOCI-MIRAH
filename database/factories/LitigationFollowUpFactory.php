<?php

namespace Database\Factories;

use App\Enums\FollowUpType;
use App\Models\InfractionCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class LitigationFollowUpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'infraction_case_id' => InfractionCase::factory(),
            'type' => FollowUpType::Warning,
            'opened_at' => now(),
        ];
    }
}
