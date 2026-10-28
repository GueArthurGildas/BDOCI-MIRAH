<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;

enum MessageLanguage: string
{
    use HasCommunicationsLabel;

    case French = 'fr';
    case English = 'en';
}
