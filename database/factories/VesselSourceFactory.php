<?php

namespace Database\Factories;

use App\Enums\MatchingStatus;
use App\Enums\SourceSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

class VesselSourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => null,
            'source' => fake()->randomElement(SourceSystem::cases()),
            'external_id' => fake()->unique()->numerify('EXT-#######'),
            'last_import_at' => now(),
            'raw_data' => [],
            'matching_status' => MatchingStatus::New,
        ];
    }
}
