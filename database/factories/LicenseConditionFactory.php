<?php

namespace Database\Factories;

use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseConditionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'body' => fake()->randomElement([
                'Embarquement d\'un observateur à bord sur demande du CSP',
                'Interdiction de pêche dans la bande des 12 milles',
                'Déclaration d\'entrée et de sortie de la ZEE obligatoire',
            ]),
        ];
    }
}
