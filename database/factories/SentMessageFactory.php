<?php

namespace Database\Factories;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageStatus;
use App\Enums\MessageTrigger;
use App\Models\CommunicationChannel;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

class SentMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'communication_channel_id' => fn () => CommunicationChannel::forCode(CommunicationChannelCode::Email)->id,
            'vessel_id' => Vessel::factory(),
            'recipient' => fake()->safeEmail(),
            'subject' => 'Rappel',
            'body' => fake()->sentence(),
            'status' => MessageStatus::Sent,
            'sent_at' => now(),
            'triggered_by' => MessageTrigger::User,
            'user_id' => User::factory(),
            'reply_received' => false,
        ];
    }

    public function status(MessageStatus $status): static
    {
        return $this->state([
            'status' => $status,
            'sent_at' => $status === MessageStatus::Prepared ? null : now(),
            'acknowledged_at' => $status === MessageStatus::Acknowledged ? now() : null,
        ]);
    }
}
