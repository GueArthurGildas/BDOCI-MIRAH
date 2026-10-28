<?php

namespace Database\Factories;

use App\Enums\AuthorizingRole;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissionAuthorizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'authorized_by' => User::factory(),
            'authorizing_role' => AuthorizingRole::Director,
            'authorized_at' => now(),
        ];
    }
}
