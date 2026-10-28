<?php

namespace Database\Factories;

use App\Models\AuthorizedListPublication;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorizedListDistributionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'authorized_list_publication_id' => AuthorizedListPublication::factory(),
            'recipient' => fake()->safeEmail(),
            'sent_at' => now(),
        ];
    }
}
