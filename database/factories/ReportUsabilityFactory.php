<?php

namespace Database\Factories;

use App\Models\WitnessReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportUsabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'witness_report_id' => WitnessReport::factory(),
            'is_usable' => true,
            'interview_report' => fake()->paragraph(),
        ];
    }
}
