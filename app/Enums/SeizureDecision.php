<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum SeizureDecision: string
{
    use HasTranslatedLabel;

    case Return = 'return';
    case Destruction = 'destruction';
    case Confiscation = 'confiscation';
}
