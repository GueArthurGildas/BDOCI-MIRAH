<?php

namespace Database\Factories;

use App\Enums\ObservedUnitLevel;
use App\Enums\SightingSource;
use App\Models\Mission;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObservedUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'vessel_id' => Vessel::factory(),
            'level' => ObservedUnitLevel::Sighted,
            'sighting_source' => SightingSource::Visual,
            'observed_at' => now(),
        ];
    }
}
