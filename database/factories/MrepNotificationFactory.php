<?php

namespace Database\Factories;

use App\Models\MrepDpep;
use Illuminate\Database\Eloquent\Factories\Factory;

class MrepNotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dpep_id' => MrepDpep::factory(),
            'recipient' => fake()->randomElement(['Etat du pavillon', 'ICCAT', 'Autorité portuaire']),
            'channel' => 'email',
            'notified_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'status' => 'sent',
        ];
    }
}
