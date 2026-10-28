<?php

namespace Database\Factories;

use App\Models\ExternalInspection;
use App\Models\Gear;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionGearFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inspection_id' => ExternalInspection::factory(),
            'gear_id' => Gear::factory(),
            'mesh_mm' => fake()->randomElement([60.0, 70.0, 80.0, 100.0]),
            'is_marking_compliant' => true,
            'is_compliant' => true,
        ];
    }
}
