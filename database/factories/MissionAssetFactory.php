<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissionAssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'asset_id' => Asset::factory(),
        ];
    }
}
