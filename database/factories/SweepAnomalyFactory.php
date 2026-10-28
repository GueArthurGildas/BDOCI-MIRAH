<?php

namespace Database\Factories;

use App\Enums\SweepAnomalyType;
use App\Models\Sweep;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SweepAnomalyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sweep_id' => Sweep::factory(),
            'vessel_id' => Vessel::factory(),
            'type' => SweepAnomalyType::NonEmitting,
        ];
    }
}
