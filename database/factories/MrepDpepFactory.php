<?php

namespace Database\Factories;

use App\Enums\MrepPurpose;
use App\Models\Port;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MrepDpepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'external_id' => 'DPEP-'.fake()->unique()->numerify('2026-#####'),
            'vessel_id' => Vessel::factory(),
            'number' => fake()->numerify('DPEP/ABJ/2026/####'),
            'port_id' => Port::factory(),
            'planned_arrival_at' => now()->addDays(fake()->numberBetween(1, 10)),
            'purpose' => fake()->randomElement(MrepPurpose::cases()),
            'last_port_call' => 'Dakar (SN)',
            'catches_on_board' => [['species' => 'YFT', 'weight_kg' => 18000]],
            'catches_to_land' => [['species' => 'YFT', 'weight_kg' => 12000]],
            'is_complete' => true,
            'is_72h_deadline_met' => true,
            'raw' => null,
            'read_at' => now(),
        ];
    }
}
