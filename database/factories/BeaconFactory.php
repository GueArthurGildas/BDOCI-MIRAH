<?php

namespace Database\Factories;

use App\Enums\BeaconModem;
use Illuminate\Database\Eloquent\Factories\Factory;

class BeaconFactory extends Factory
{
    public function definition(): array
    {
        return [
            'serial_number' => fake()->unique()->numerify('BCN-########'),
            'model' => fake()->randomElement(['SkyTrack 200', 'MarineLink M3', 'Thuraya FT2225']),
            'manufacturer' => fake()->randomElement(['CLS', 'Thrane', 'Satlink']),
            'supplier' => 'CLS Abidjan',
            'modem' => fake()->randomElement(BeaconModem::cases()),
            'subscription' => fake()->numerify('SUB-#####'),
        ];
    }
}
