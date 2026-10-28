<?php

namespace Database\Factories;

use App\Enums\PrecautionaryMeasureType;
use App\Models\InfractionCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrecautionaryMeasureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'infraction_case_id' => InfractionCase::factory(),
            'type' => PrecautionaryMeasureType::Immobilization,
        ];
    }
}
