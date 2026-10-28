<?php

namespace Database\Factories;

use App\Models\MrepDpep;
use Illuminate\Database\Eloquent\Factories\Factory;

class MrepPortCallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dpep_id' => MrepDpep::factory(),
            'arrival' => now()->subDays(2),
            'departure' => now()->subDay(),
            'is_inspected' => false,
            'is_infraction_recorded' => false,
            'inspection_id' => null,
        ];
    }
}
