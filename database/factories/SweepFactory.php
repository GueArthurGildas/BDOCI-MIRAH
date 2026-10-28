<?php

namespace Database\Factories;

use App\Models\ControlSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class SweepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'control_slot_id' => ControlSlot::factory(),
            'operator_user_id' => null,
            'vessels_count' => 0,
            'non_emitting_count' => 0,
        ];
    }
}
