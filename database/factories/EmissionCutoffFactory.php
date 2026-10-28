<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmissionCutoffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'started_at' => now()->subHours(8),
            'ended_at' => null,
            'is_technical_stop' => false,
        ];
    }
}
