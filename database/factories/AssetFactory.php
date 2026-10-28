<?php

namespace Database\Factories;

use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => AssetType::PatrolBoat,
            'label' => fake()->unique()->bothify('Vedette CSP ##'),
            'is_active' => true,
        ];
    }
}
