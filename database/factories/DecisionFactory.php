<?php

namespace Database\Factories;

use App\Models\LitigationFollowUp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DecisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'follow_up_id' => LitigationFollowUp::factory(),
            'label' => fake()->sentence(4),
            'decided_by' => User::factory(),
            'decided_at' => now(),
            'is_penalty' => false,
        ];
    }
}
