<?php

namespace Database\Factories;

use App\Enums\PositionLoadingSource;
use App\Enums\SlotTestResult;
use App\Models\ControlSlot;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlotPositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'control_slot_id' => ControlSlot::factory(),
            'vessel_id' => Vessel::factory(),
            'latitude' => fake()->randomFloat(6, 4.0, 5.0),
            'longitude' => fake()->randomFloat(6, -7.0, -4.0),
            'speed' => fake()->randomFloat(2, 0, 10),
            'last_emission_at' => now()->subMinutes(20),
            'emission_test' => SlotTestResult::ToConfirm,
            'speed_activity_test' => SlotTestResult::ToConfirm,
            'trajectory_test' => SlotTestResult::ToConfirm,
            'loading_source' => PositionLoadingSource::Api,
        ];
    }
}
