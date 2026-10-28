<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissedDeclarationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory()->state(['flag' => 'ES']),
            'detected_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'reminders' => 0,
        ];
    }
}
