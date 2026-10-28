<?php

namespace Database\Factories;

use App\Enums\OperationStatus;
use App\Models\Operation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationArchiveFactory extends Factory
{
    public function definition(): array
    {
        return [
            'operation_id' => Operation::factory()->state(['status' => OperationStatus::Archived, 'ended_at' => now()->subYears(6)]),
            'archived_at' => now(),
        ];
    }
}
