<?php

namespace Database\Factories;

use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertHandlingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'alert_id' => Alert::factory(),
            'verification_questions' => null,
            'is_vessel_contacted' => false,
            'measures' => null,
            'handled_at' => now(),
        ];
    }
}
