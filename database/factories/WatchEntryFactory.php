<?php

namespace Database\Factories;

use App\Enums\WatchOrigin;
use App\Enums\WatchStatus;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class WatchEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'origin' => WatchOrigin::DirectorListManual,
            'started_at' => now()->subHours(fake()->numberBetween(1, 72)),
            'status' => WatchStatus::Open,
        ];
    }
}
