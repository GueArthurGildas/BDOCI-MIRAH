<?php

namespace Database\Factories;

use App\Enums\ExternalListType;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalListFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'type' => ExternalListType::Rfmo,
            'label' => fake()->words(3, true),
        ];
    }
}
