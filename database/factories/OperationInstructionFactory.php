<?php

namespace Database\Factories;

use App\Models\Operation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationInstructionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'operation_id' => Operation::factory(),
            'body' => fake()->sentence(),
            'given_by' => User::factory(),
        ];
    }
}
