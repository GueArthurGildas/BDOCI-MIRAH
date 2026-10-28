<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;

enum CommunicationChannelCode: string
{
    use HasCommunicationsLabel;

    case InApp = 'in_app';
    case Email = 'email';
    case WhatsApp = 'whatsapp';
}
