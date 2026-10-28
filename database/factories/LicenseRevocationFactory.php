<?php

namespace Database\Factories;

use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseRevocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'license_id' => License::factory()->revoked(),
            'reason' => 'Pêche en zone interdite',
            'revoked_on' => today()->subWeek(),
            'is_repeat_offense' => false,
        ];
    }
}
