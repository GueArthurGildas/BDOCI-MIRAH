<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->slug(2).'.pdf',
            'mime_type' => 'application/pdf',
            'path' => 'documents/'.fake()->uuid().'.pdf',
            'is_frozen' => false,
        ];
    }
}
