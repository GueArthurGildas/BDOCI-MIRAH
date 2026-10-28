<?php

namespace Database\Factories;

use App\Enums\LicenseImportStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseImportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status' => LicenseImportStatus::Preview,
            'created_by' => User::factory(),
        ];
    }
}
