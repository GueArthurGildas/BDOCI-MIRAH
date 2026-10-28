<?php

namespace Database\Factories;

use App\Models\ExternalInspection;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeclaredObservedCatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inspection_id' => ExternalInspection::factory(),
            'species_id' => Species::factory(),
            'presentation_id' => null,
            'declared_weight_kg' => 1000,
            'observed_weight_kg' => 1000,
            'net_weight_kg' => 1000,
            'conversion_factor' => 1,
            'variance_pct' => 0,
            'exceeds_csp_threshold' => false,
        ];
    }
}
