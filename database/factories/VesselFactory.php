<?php

namespace Database\Factories;

use App\Enums\VesselCategory;
use App\Enums\VesselStatus;
use App\Enums\VesselType;
use Illuminate\Database\Eloquent\Factories\Factory;

class VesselFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => mb_strtoupper(fake()->unique()->words(2, true)),
            'registration_number' => fake()->unique()->bothify('AN-####-??'),
            'imo_number' => fake()->unique()->numerify('IMO#######'),
            'call_sign' => fake()->unique()->bothify('TU##??'),
            'flag' => 'CI',
            'type' => fake()->randomElement([VesselType::Trawler, VesselType::Seiner, VesselType::Longliner]),
            'category' => VesselCategory::Fishing,
            'owner' => fake()->company(),
            'length_meters' => fake()->randomFloat(2, 12, 80),
            'gross_tonnage' => fake()->randomFloat(2, 20, 1500),
            'status' => VesselStatus::Active,
        ];
    }

    public function patrol(): static
    {
        return $this->state([
            'category' => VesselCategory::Patrol,
            'type' => VesselType::PatrolBoat,
            'imo_number' => null,
        ]);
    }
}
