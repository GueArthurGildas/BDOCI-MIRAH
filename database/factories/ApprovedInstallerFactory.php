<?php

namespace Database\Factories;

use App\Models\LegalEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovedInstallerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'legal_entity_id' => LegalEntity::factory(),
            'approved_until' => today()->addYear(),
        ];
    }
}
