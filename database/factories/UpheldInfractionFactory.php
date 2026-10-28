<?php

namespace Database\Factories;

use App\Models\InfractionCase;
use App\Models\InfractionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class UpheldInfractionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'infraction_case_id' => InfractionCase::factory(),
            'infraction_type_id' => InfractionType::factory(),
            'upheld_at' => now(),
        ];
    }
}
