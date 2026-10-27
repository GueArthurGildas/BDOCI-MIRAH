<?php

namespace Database\Factories;

use App\Enums\ConnectorCommentKind;
use App\Models\DataSource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataSourceCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'data_source_id' => DataSource::factory(),
            'user_id' => User::factory(),
            'kind' => ConnectorCommentKind::Note,
            'body' => fake()->sentence(12),
        ];
    }
}
