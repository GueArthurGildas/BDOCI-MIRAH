<?php

namespace Database\Factories;

use App\Enums\CaptainSignatureStatus;
use App\Enums\InspectionLocationType;
use App\Models\Port;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalInspectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numerify('EI-2026-####'),
            'reference' => fake()->unique()->numerify('CSP/ABJ/2026/####'),
            'vessel_id' => Vessel::factory(),
            'location_type' => InspectionLocationType::Port,
            'port_id' => Port::factory(),
            'started_at' => now()->subDays(fake()->numberBetween(1, 120)),
            'ended_at' => null,
            'control_type' => 'routine',
            'result' => 'compliant',
            'captain_signature_status' => CaptainSignatureStatus::Signed,
            'is_copy_handed_over' => true,
            'raw' => null,
            'read_at' => now(),
        ];
    }
}
