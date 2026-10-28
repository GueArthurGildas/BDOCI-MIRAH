<?php

namespace Database\Factories;

use App\Models\Mission;
use App\Models\Observer;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObserverBoardingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'observer_id' => Observer::factory(),
            'vessel_id' => Vessel::factory(),
            'started_on' => today(),
        ];
    }
}
