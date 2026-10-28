<?php

namespace Database\Factories;

use App\Models\ExternalInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionInfractionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inspection_id' => ExternalInspection::factory(),
            'provision' => 'Loi n° 2016-554, art. 42',
            'stamp' => fake()->numerify('SC-####'),
            'measures' => 'Engin saisi à titre conservatoire',
        ];
    }
}
