<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VesselHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'former_name' => fake()->words(2, true),
            'changed_on' => fake()->date(),
        ];
    }
}
