<?php

namespace Database\Factories;

use App\Enums\SuspensionReason;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseSuspensionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'license_id' => License::factory()->suspended(),
            'reason' => SuspensionReason::Art105,
            'started_on' => today()->subWeeks(2),
            'planned_end_on' => today()->addWeeks(6),
        ];
    }

    public function lifted(): static
    {
        return $this->state(['lifted_on' => today()->subDay()]);
    }
}
