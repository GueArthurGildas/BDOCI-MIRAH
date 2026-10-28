<?php

namespace Database\Factories;

use App\Enums\CommunicationChannelCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunicationChannelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => CommunicationChannelCode::Email,
            'is_auto_dispatch_enabled' => false,
            'is_consent_required' => false,
        ];
    }
}
