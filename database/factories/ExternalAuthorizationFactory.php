<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalAuthorizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'institution_id' => Institution::factory(),
            'number' => fake()->bothify('AUT-####-??'),
            'valid_from' => today()->subMonths(3),
            'valid_to' => today()->addMonths(9),
        ];
    }
}
