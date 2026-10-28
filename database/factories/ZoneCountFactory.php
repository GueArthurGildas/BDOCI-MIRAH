<?php

namespace Database\Factories;

use App\Models\ControlSlot;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneCountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'control_slot_id' => ControlSlot::factory(),
            'zone_id' => Zone::factory(),
            'vessels_count' => fake()->numberBetween(0, 20),
        ];
    }
}
