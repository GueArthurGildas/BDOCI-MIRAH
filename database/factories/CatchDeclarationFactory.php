<?php

namespace Database\Factories;

use App\Enums\CatchSource;
use App\Models\Species;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class CatchDeclarationFactory extends Factory
{
    public function definition(): array
    {
        $periodStart = today()->subDays(fake()->numberBetween(7, 300));

        return [
            'vessel_id' => Vessel::factory(),
            'species_id' => Species::factory(),
            'period_start' => $periodStart,
            'period_end' => $periodStart->copy()->addDays(7),
            'quantity_kg' => fake()->randomFloat(2, 100, 20000),
            'source' => CatchSource::Logbook,
        ];
    }
}
