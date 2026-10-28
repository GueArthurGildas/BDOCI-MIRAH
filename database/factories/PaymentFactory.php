<?php

namespace Database\Factories;

use App\Models\Decision;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'decision_id' => Decision::factory(),
            'amount' => fake()->numberBetween(500000, 15000000),
            'currency' => 'XOF',
            'paid_on' => today(),
        ];
    }
}
