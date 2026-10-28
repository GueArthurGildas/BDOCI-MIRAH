<?php

namespace Database\Factories;

use App\Enums\LicenseOrigin;
use App\Enums\LicenseRecordStatus;
use App\Enums\LicenseSegment;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseFactory extends Factory
{
    public function definition(): array
    {
        $issuedOn = today()->subMonths(fake()->numberBetween(1, 10));

        return [
            'vessel_id' => Vessel::factory(),
            'number' => fake()->unique()->bothify('LIC-####-####'),
            'segment' => LicenseSegment::Industrial,
            'issued_on' => $issuedOn,
            'expires_on' => $issuedOn->copy()->addYear(),
            'status' => LicenseRecordStatus::Active,
            'origin' => LicenseOrigin::Manual,
        ];
    }

    public function expired(): static
    {
        return $this->state([
            'issued_on' => today()->subYears(2),
            'expires_on' => today()->subYear(),
        ]);
    }

    public function expiringIn(int $days): static
    {
        return $this->state([
            'issued_on' => today()->addDays($days)->subYear(),
            'expires_on' => today()->addDays($days),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(['status' => LicenseRecordStatus::Suspended]);
    }

    public function revoked(): static
    {
        return $this->state(['status' => LicenseRecordStatus::Revoked]);
    }
}
