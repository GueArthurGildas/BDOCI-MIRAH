<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum AlertRuleType: string
{
    use HasTranslatedLabel;

    case Geofencing = 'geofencing';
    case Silence = 'silence';
    case Speed = 'speed';
    case Zone = 'zone';
    case Other = 'other';
}
