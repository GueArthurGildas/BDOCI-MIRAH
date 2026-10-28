<?php

namespace Database\Factories;

use App\Enums\IdentifierType;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VesselIdentifierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'type' => IdentifierType::Mmsi,
            'value' => fake()->unique()->numerify('6########'),
        ];
    }
}
