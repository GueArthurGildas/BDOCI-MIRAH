<?php

namespace Database\Factories;

use App\Models\VmsFailure;
use Illuminate\Database\Eloquent\Factories\Factory;

class CessationOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vms_failure_id' => VmsFailure::factory(),
            'ordered_at' => now(),
            'includes_suspension' => false,
            'requires_port_return' => true,
        ];
    }
}
