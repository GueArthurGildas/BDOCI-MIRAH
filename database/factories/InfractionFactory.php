<?php

namespace Database\Factories;

use App\Enums\InfractionStatus;
use App\Models\InfractionType;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class InfractionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => fake()->unique()->bothify('PV-####-###'),
            'vessel_id' => Vessel::factory(),
            'infraction_type_id' => InfractionType::factory(),
            'detected_at' => now()->subDays(fake()->numberBetween(1, 200)),
            'facts' => fake()->paragraph(),
            'status' => InfractionStatus::Reported,
        ];
    }
}
