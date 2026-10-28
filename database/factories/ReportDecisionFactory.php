<?php

namespace Database\Factories;

use App\Enums\ReportDecisionType;
use App\Models\User;
use App\Models\WitnessReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportDecisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'witness_report_id' => WitnessReport::factory(),
            'decision' => ReportDecisionType::Dismissal,
            'decided_by' => User::factory(),
            'decided_at' => now(),
        ];
    }
}
