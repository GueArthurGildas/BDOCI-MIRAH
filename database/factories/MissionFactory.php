<?php

namespace Database\Factories;

use App\Enums\MissionStatus;
use App\Enums\MissionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissionFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = now()->subDays(fake()->numberBetween(1, 120));

        return [
            'reference' => fake()->unique()->bothify('MIS-####-###'),
            'type' => fake()->randomElement(MissionType::cases()),
            'objective' => fake()->sentence(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addDays(2),
            'status' => MissionStatus::Completed,
        ];
    }
}
