<?php

namespace Database\Factories;

use App\Models\WitnessReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportedVesselFactory extends Factory
{
    public function definition(): array
    {
        return [
            'witness_report_id' => WitnessReport::factory(),
            'rank' => 1,
            'name' => fake()->words(2, true),
            'is_fishing' => true,
        ];
    }
}
