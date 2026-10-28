<?php

namespace Database\Factories;

use App\Enums\DataSourceIndicator;
use App\Enums\DataSourceMode;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataSourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'mode' => DataSourceMode::Api,
            'indicator_status' => DataSourceIndicator::Green,
            'last_synced_at' => now()->subMinutes(10),
            'is_stale' => false,
        ];
    }

    public function stale(): static
    {
        return $this->state(fn () => ['is_stale' => true, 'indicator_status' => DataSourceIndicator::Orange, 'last_synced_at' => now()->subDays(3)]);
    }
}
