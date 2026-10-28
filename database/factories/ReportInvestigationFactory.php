<?php

namespace Database\Factories;

use App\Models\WitnessReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportInvestigationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'witness_report_id' => WitnessReport::factory(),
            'elements' => ['photos' => true],
            'notes' => fake()->sentence(),
        ];
    }
}
