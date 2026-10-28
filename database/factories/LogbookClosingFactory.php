<?php

namespace Database\Factories;

use App\Models\LogbookDay;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogbookClosingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'logbook_day_id' => LogbookDay::factory(),
            'counters' => ['entries_total' => 0],
            'closed_by' => User::factory(),
            'closed_at' => now(),
        ];
    }
}
