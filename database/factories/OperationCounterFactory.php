<?php

namespace Database\Factories;

use App\Models\OperationCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationCounterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => OperationCategory::factory(),
            'year' => now()->year,
            'last_number' => 0,
        ];
    }
}
