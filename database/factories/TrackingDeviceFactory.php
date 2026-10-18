<?php

namespace Database\Factories;

use App\Enums\TrackingSystem;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackingDeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'system' => TrackingSystem::Vms,
            'identifier' => fake()->unique()->numerify('VMS-########'),
            'installed_at' => today()->subYears(2),
        ];
    }
}
