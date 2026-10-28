<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum FleetType: string
{
    use HasTranslatedLabel;

    case Fishing = 'fishing';
    case Support = 'support';
    case Reefer = 'reefer';
    case Other = 'other';
}
