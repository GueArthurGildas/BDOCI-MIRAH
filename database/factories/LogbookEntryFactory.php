<?php

namespace Database\Factories;

use App\Enums\LogbookDirection;
use App\Enums\LogbookHandlingStatus;
use App\Models\LogbookDay;
use App\Models\User;
use App\Support\Logbook\LogbookChannels;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogbookEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'logbook_day_id' => LogbookDay::factory(),
            'occurred_at' => now()->subMinutes(fake()->numberBetween(5, 300)),
            'channel_id' => fn () => app(LogbookChannels::class)->idFor('phone'),
            'content' => fake()->sentence(),
            'direction' => LogbookDirection::Received,
            'is_order_or_decision' => false,
            'handling_status' => LogbookHandlingStatus::ToHandle,
            'entered_by' => User::factory(),
        ];
    }
}
