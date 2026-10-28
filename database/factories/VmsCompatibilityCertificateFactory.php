<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VmsCompatibilityCertificateFactory extends Factory
{
    public function definition(): array
    {
        $issuedOn = today()->subMonths(fake()->numberBetween(1, 10));

        return [
            'vessel_id' => Vessel::factory(),
            'issued_on' => $issuedOn,
            'expires_on' => $issuedOn->copy()->addYear(),
        ];
    }
}
