<?php

namespace Database\Factories;

use App\Models\VmsFailure;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkingOrderAttestationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vms_failure_id' => VmsFailure::factory(),
            'document_id' => null,
            'attested_on' => today(),
        ];
    }
}
