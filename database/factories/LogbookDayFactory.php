<?php

namespace Database\Factories;

use App\Enums\LogbookDayStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogbookDayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'day' => fn () => today()->subDays(fake()->unique()->numberBetween(1, 3000))->toDateString(),
            'shift_start_at' => fn (array $attributes) => now()->parse($attributes['day'])->setTime(8, 0),
            'shift_end_at' => fn (array $attributes) => now()->parse($attributes['day'])->setTime(18, 0),
            'status' => LogbookDayStatus::Open,
        ];
    }

    public function locked(): static
    {
        return $this->state(['status' => LogbookDayStatus::Locked, 'locked_at' => now()]);
    }
}
