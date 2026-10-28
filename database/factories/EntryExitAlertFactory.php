<?php

namespace Database\Factories;

use App\Enums\EezDeclarationType;
use App\Enums\EntryExitAlertStatus;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntryExitAlertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory()->state(['flag' => 'ES']),
            'direction' => EezDeclarationType::Entry,
            'alerted_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'status' => EntryExitAlertStatus::Open,
        ];
    }
}
