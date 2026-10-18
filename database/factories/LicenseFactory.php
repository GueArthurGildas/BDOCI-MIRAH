<?php

namespace Database\Factories;

use App\Enums\LicenseType;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseFactory extends Factory
{
    public function definition(): array
    {
        $validFrom = today()->subMonths(fake()->numberBetween(1, 10));

        return [
            'vessel_id' => Vessel::factory(),
            'number' => fake()->unique()->bothify('LIC-####-####'),
            'type' => LicenseType::Industrial,
            'valid_from' => $validFrom,
            'valid_until' => $validFrom->copy()->addYear(),
        ];
    }

    public function expired(): static
    {
        return $this->state([
            'valid_from' => today()->subYears(2),
            'valid_until' => today()->subYear(),
        ]);
    }

    public function expiringIn(int $days): static
    {
        return $this->state([
            'valid_from' => today()->addDays($days)->subYear(),
            'valid_until' => today()->addDays($days),
        ]);
    }
}
