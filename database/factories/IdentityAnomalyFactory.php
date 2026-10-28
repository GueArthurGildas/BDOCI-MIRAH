<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdentityAnomalyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'detail' => 'AIS name differs from the declared name',
            'detected_at' => now(),
        ];
    }
}
