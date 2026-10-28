<?php

namespace Database\Factories;

use App\Models\Decision;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'decision_id' => Decision::factory(),
            'filed_on' => today(),
        ];
    }
}
