<?php

namespace Database\Factories;

use App\Enums\DataSourceKind;
use App\Enums\DataSourceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataSourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'kind' => DataSourceKind::Api,
            'status' => DataSourceStatus::Operational,
            'last_synced_at' => now()->subMinutes(10),
        ];
    }
}
