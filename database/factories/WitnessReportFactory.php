<?php

namespace Database\Factories;

use App\Enums\ReportChannel;
use App\Enums\WitnessReportStatus;
use App\Models\Witness;
use Illuminate\Database\Eloquent\Factories\Factory;

class WitnessReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'received_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'witness_id' => Witness::factory(),
            'latitude' => fake()->latitude(3.5, 5.0),
            'longitude' => fake()->longitude(-7.5, -3.0),
            'channel' => ReportChannel::Email,
            'status' => WitnessReportStatus::Received,
        ];
    }
}
