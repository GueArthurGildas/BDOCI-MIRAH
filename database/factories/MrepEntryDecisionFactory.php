<?php

namespace Database\Factories;

use App\Enums\EntryDecision;
use App\Models\MrepDpep;
use Illuminate\Database\Eloquent\Factories\Factory;

class MrepEntryDecisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dpep_id' => MrepDpep::factory(),
            'decision' => EntryDecision::Authorization,
            'decided_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'decision_maker' => 'Direction des pêches maritimes',
        ];
    }
}
