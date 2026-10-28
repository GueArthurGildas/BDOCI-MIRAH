<?php

namespace Database\Factories;

use App\Enums\ExternalLinkSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'source' => fake()->unique()->randomElement(ExternalLinkSource::cases()),
            'model_url' => 'https://example.org/vessels/{id}',
            'is_copy_fallback' => true,
        ];
    }
}
