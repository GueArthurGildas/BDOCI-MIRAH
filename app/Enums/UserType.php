<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum UserType: string
{
    use HasTranslatedLabel;

    case Human = 'human';
    case AiAgent = 'ai_agent';
    case System = 'system';
}
