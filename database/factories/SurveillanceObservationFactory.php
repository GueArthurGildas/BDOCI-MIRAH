<?php

namespace Database\Factories;

use App\Enums\SurveillanceObservationStatus;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveillanceObservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'body' => fake()->paragraph(),
            'status' => SurveillanceObservationStatus::Monitoring,
            'created_by' => User::factory(),
            'created_at' => now()->subMinutes(fake()->numberBetween(5, 600)),
        ];
    }
}
