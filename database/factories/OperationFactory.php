<?php

namespace Database\Factories;

use App\Enums\OperationStatus;
use App\Models\OperationCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => OperationCategory::factory(),
            'number' => fn () => fake()->unique()->numberBetween(1, 999),
            'year' => now()->year,
            'code' => fn (array $attributes) => sprintf('OPS-%03d-%s-%d-%s', $attributes['number'], 'DIV', $attributes['year'], 'TEST'),
            'name' => fake()->words(2, true),
            'status' => OperationStatus::Open,
            'started_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'summary' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
