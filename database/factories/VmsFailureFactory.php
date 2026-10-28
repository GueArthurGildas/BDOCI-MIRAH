<?php

namespace Database\Factories;

use App\Enums\VmsFailureCategory;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VmsFailureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'category' => VmsFailureCategory::Ivorian,
            'started_at' => now()->subDays(2),
            'report_frequency_h' => 4,
            'repair_deadline_days' => 10,
        ];
    }
}
