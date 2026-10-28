<?php

namespace Database\Factories;

use App\Enums\LicenseImportLineStatus;
use App\Models\LicenseImport;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseImportLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'license_import_id' => LicenseImport::factory(),
            'vessel_id' => Vessel::factory(),
            'data' => [
                'number' => fake()->unique()->bothify('LIC-####-####'),
                'segment' => 'industrial',
                'issued_on' => today()->toDateString(),
                'expires_on' => today()->addYear()->toDateString(),
            ],
            'status' => LicenseImportLineStatus::ToValidate,
        ];
    }
}
