<?php

namespace Database\Factories;

use App\Models\ShiftHandover;
use App\Models\SurveillanceObservation;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shift_handover_id' => ShiftHandover::factory(),
            'surveillance_observation_id' => SurveillanceObservation::factory(),
            'next_team_id' => Team::factory(),
        ];
    }
}
