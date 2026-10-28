<?php

namespace Database\Factories;

use App\Enums\MirrorOrigin;
use App\Models\Port;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class LandingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'port_id' => Port::factory(),
            'landed_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'origin' => MirrorOrigin::EInspection,
            'lines' => [['species' => 'YFT', 'weight_kg' => 12000]],
        ];
    }
}
