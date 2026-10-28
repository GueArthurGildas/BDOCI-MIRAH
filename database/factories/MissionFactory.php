<?php

namespace Database\Factories;

use App\Enums\MissionNature;
use App\Enums\MissionStatus;
use App\Enums\MissionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissionFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->subDays(fake()->numberBetween(1, 120));

        return [
            'code' => fake()->unique()->numerify('MIS-2026-####'),
            'nature' => MissionNature::Internal,
            'type' => MissionType::Maritime,
            'objectives' => fake()->sentence(),
            'planned_start_at' => $start,
            'planned_end_at' => $start->copy()->addDays(2),
            'status' => MissionStatus::Scheduled,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'actual_start_at' => $attributes['planned_start_at'],
            'actual_end_at' => $attributes['planned_end_at'],
            'status' => MissionStatus::Completed,
        ]);
    }
}
