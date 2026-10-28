<?php

namespace Database\Factories;

use App\Models\EezDeclaration;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

class EezDeclarationCatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'eez_declaration_id' => EezDeclaration::factory(),
            'species_id' => Species::factory(),
            'weight_kg' => fake()->randomFloat(2, 500, 40000),
        ];
    }
}
