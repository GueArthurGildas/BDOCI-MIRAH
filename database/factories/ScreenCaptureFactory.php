<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScreenCaptureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'document_id' => Document::factory(),
            'taken_at' => now(),
        ];
    }
}
