<?php

namespace Database\Factories;

use App\Models\DataSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataSourceSyncFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = now()->subMinutes(fake()->numberBetween(10, 600));

        return [
            'data_source_id' => DataSource::factory(),
            'started_at' => $startedAt,
            'finished_at' => $startedAt->copy()->addSeconds(fake()->numberBetween(5, 90)),
            'received_count' => fake()->numberBetween(20, 400),
            'rejected_count' => 0,
            'message' => null,
        ];
    }
}
