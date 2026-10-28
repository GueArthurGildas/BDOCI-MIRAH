<?php

namespace Database\Factories;

use App\Enums\MirrorOrigin;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransshipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'partner_vessel_id' => Vessel::factory(),
            'transshipped_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'origin' => MirrorOrigin::EInspection,
            'lines' => [['species' => 'SKJ', 'weight_kg' => 8000]],
        ];
    }
}
