<?php

namespace Database\Factories;

use App\Enums\ActorRole;
use App\Models\LegalEntity;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VesselActorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'role' => ActorRole::Shipowner,
            'legal_entity_id' => LegalEntity::factory(),
            'started_on' => fake()->date(),
        ];
    }
}
