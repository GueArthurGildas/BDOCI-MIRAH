<?php

namespace Database\Factories;

use App\Enums\ConnectorCommentKind;
use App\Models\Comment;
use App\Models\DataSource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Comment> */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'object_type' => (new DataSource)->getMorphClass(),
            'object_id' => DataSource::factory(),
            'user_id' => User::factory(),
            'kind' => ConnectorCommentKind::Note->value,
            'body' => fake()->sentence(12),
        ];
    }

    public function forDataSource(DataSource $dataSource): static
    {
        return $this->state(['object_type' => $dataSource->getMorphClass(), 'object_id' => $dataSource->id]);
    }
}
