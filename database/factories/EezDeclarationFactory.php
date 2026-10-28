<?php

namespace Database\Factories;

use App\Enums\EezDeclarationStatus;
use App\Enums\EezDeclarationType;
use App\Enums\EezEntryOrigin;
use App\Enums\EezReason;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EezDeclarationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory()->state(['flag' => 'ES']),
            'type' => EezDeclarationType::Entry,
            'received_at' => now()->subHours(fake()->numberBetween(1, 120)),
            'entry_origin' => EezEntryOrigin::Manual,
            'crossed_at' => now()->subHours(fake()->numberBetween(1, 120)),
            'latitude' => fake()->randomFloat(6, 2.2, 4.2),
            'longitude' => fake()->randomFloat(6, -7.3, -3.0),
            'speed' => fake()->randomFloat(2, 3, 12),
            'reason' => fake()->randomElement(EezReason::cases()),
            'status' => EezDeclarationStatus::Received,
        ];
    }

    public function exit(): static
    {
        return $this->state(['type' => EezDeclarationType::Exit]);
    }

    public function unidentified(): static
    {
        return $this->state(['vessel_id' => null, 'declared_name' => mb_strtoupper(fake()->words(2, true))]);
    }
}
