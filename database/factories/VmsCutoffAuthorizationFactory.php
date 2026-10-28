<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VmsCutoffAuthorizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'started_at' => now()->subDay(),
            'ended_at' => now()->addDays(2),
            'reason' => 'Technical stop in port',
        ];
    }
}
