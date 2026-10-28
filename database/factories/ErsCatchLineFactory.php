<?php

namespace Database\Factories;

use App\Models\ErsDeclaration;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

class ErsCatchLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ers_declaration_id' => ErsDeclaration::factory(),
            'species_id' => Species::factory(),
            'presentation_id' => null,
            'conversion_factor' => null,
            'live_weight_kg' => fake()->randomFloat(2, 100, 5000),
            'fao_zone' => '34.3.5',
            'gear_id' => null,
        ];
    }
}
