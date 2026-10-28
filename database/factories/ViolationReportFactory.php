<?php

namespace Database\Factories;

use App\Models\InfractionCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class ViolationReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'infraction_case_id' => InfractionCase::factory(),
            'number' => fake()->unique()->numerify('PV-2026-####'),
            'is_signature_refused' => false,
        ];
    }
}
