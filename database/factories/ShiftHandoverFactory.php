<?php

namespace Database\Factories;

use App\Models\Rotation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftHandoverFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rotation_id' => Rotation::factory(),
            'started_at' => now(),
        ];
    }
}
