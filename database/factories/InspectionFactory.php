<?php

namespace Database\Factories;

use App\Enums\InspectionResult;
use App\Enums\InspectionType;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'external_reference' => fake()->unique()->bothify('EI-####-#####'),
            'type' => fake()->randomElement(InspectionType::cases()),
            'inspected_at' => now()->subDays(fake()->numberBetween(1, 300)),
            'inspectors' => fake()->name(),
            'result' => InspectionResult::Compliant,
            'findings' => fake()->sentence(),
        ];
    }
}
