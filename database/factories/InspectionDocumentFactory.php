<?php

namespace Database\Factories;

use App\Enums\InspectionDocumentType;
use App\Models\ExternalInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inspection_id' => ExternalInspection::factory(),
            'type' => fake()->randomElement(InspectionDocumentType::cases()),
            'is_compliant' => true,
        ];
    }
}
