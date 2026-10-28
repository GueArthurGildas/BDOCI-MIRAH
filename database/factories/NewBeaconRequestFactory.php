<?php

namespace Database\Factories;

use App\Enums\BeaconRequestStatus;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewBeaconRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'requested_on' => today()->subDays(fake()->numberBetween(1, 30)),
            'status' => BeaconRequestStatus::Requested,
        ];
    }
}
