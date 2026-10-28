<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum MirrorOrigin: string
{
    use HasTranslatedLabel;

    case EInspection = 'e_inspection';
    case FallbackEntry = 'fallback_entry';
}
