<?php

namespace Database\Factories;

use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatrolTrackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
        ];
    }
}
