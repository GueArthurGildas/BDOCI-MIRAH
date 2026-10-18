<?php

namespace Database\Factories;

use App\Enums\ObservationLevel;
use App\Enums\ObservationStatus;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'author_id' => User::factory(),
            'observed_at' => now()->subHours(fake()->numberBetween(1, 72)),
            'description' => fake()->paragraph(),
            'level' => ObservationLevel::Warning,
            'status' => ObservationStatus::Open,
        ];
    }
}
