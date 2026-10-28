<?php

namespace Database\Factories;

use App\Enums\ErsSource;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ErsDeclarationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'declared_at' => now()->subDay(),
            'source' => ErsSource::ManualEntry,
        ];
    }
}
