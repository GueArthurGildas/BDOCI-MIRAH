<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;

enum NoteRelay: string
{
    use HasCommunicationsLabel;

    case Email = 'email';
    case WhatsApp = 'whatsapp';
}
