<?php

namespace Database\Factories;

use App\Enums\AiAgentActionStatus;
use App\Models\ActionCatalogEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiAgentActionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'action_id' => ActionCatalogEntry::first()?->id ?? throw new \LogicException('The action catalog is empty.'),
            'requested_by' => User::factory(),
            'instruction' => fake()->sentence(),
            'parameters' => [],
            'status' => AiAgentActionStatus::Requested,
        ];
    }
}
