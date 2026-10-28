<?php

namespace Database\Factories;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageForm;
use App\Enums\MessageLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'channel' => CommunicationChannelCode::Email,
            'language' => MessageLanguage::French,
            'subject' => 'Rappel pour le navire {vessel_name}',
            'body' => 'Bonjour, le navire {vessel_name} ({registration_number}) est concerné par le message du {date}.',
            'form' => MessageForm::None,
            'is_active' => true,
        ];
    }

    public function whatsapp(): static
    {
        return $this->state(['channel' => CommunicationChannelCode::WhatsApp, 'subject' => null]);
    }
}
