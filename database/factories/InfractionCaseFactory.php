<?php

namespace Database\Factories;

use App\Enums\InfractionCaseStatus;
use App\Enums\InfractionQualification;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class InfractionCaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => fake()->unique()->numerify('DI-2026-####'),
            'vessel_id' => Vessel::factory(),
            'qualification' => InfractionQualification::Presumed,
            'facts' => fake()->paragraph(),
            'status' => InfractionCaseStatus::Open,
            'opened_at' => now()->subDays(fake()->numberBetween(1, 200)),
        ];
    }
}
