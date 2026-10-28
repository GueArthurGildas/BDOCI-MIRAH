<?php

namespace Database\Factories;

use App\Models\Operation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'operation_id' => Operation::factory(),
            'occurred_at' => now()->subMinutes(fake()->numberBetween(5, 600)),
            'body' => fake()->sentence(),
        ];
    }
}
