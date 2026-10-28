<?php

namespace Database\Factories;

use App\Enums\ExportFormat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExportLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => 'operations',
            'permission' => 'reports.export_operations',
            'format' => ExportFormat::Csv,
            'filters' => [],
            'row_count' => fake()->numberBetween(0, 200),
            'exported_at' => now(),
        ];
    }
}
