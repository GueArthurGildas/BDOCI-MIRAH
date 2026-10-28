<?php

namespace Database\Factories;

use App\Enums\ZoneRuleSegment;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'segment' => fake()->randomElement(ZoneRuleSegment::cases()),
            'nautical_miles' => fake()->randomElement([1, 3, 4, 12]),
        ];
    }
}
