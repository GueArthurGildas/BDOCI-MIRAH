<?php

namespace Database\Factories;

use App\Models\Beacon;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class BeaconAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'beacon_id' => Beacon::factory(),
            'started_on' => today()->subMonths(6),
            'ended_on' => null,
            'replacement_reason' => null,
        ];
    }
}
