<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum UserKind: string
{
    use HasTranslatedLabel;

    case Human = 'human';
    case AiAgent = 'ai_agent';
    case System = 'system';
}
