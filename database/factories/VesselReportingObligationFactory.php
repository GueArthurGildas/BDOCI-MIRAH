<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VesselReportingObligationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'requires_vms' => true,
            'requires_ais' => false,
            'requires_ers' => false,
            'requires_eez_declaration' => false,
            'requires_fishing_log' => true,
        ];
    }
}
