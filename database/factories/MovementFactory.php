<?php

namespace Database\Factories;

use App\Enums\MovementDirection;
use App\Models\Port;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'port_id' => Port::factory(),
            'direction' => fake()->randomElement(MovementDirection::cases()),
            'occurred_at' => now()->subHours(fake()->numberBetween(1, 240)),
            'reason' => fake()->sentence(3),
        ];
    }
}
