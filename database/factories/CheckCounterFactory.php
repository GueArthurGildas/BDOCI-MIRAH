<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckCounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'checks_count' => 1,
            'last_checked_at' => now(),
            'is_alert_raised' => false,
        ];
    }
}
