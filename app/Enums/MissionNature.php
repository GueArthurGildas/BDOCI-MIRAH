<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum MissionNature: string
{
    use HasTranslatedLabel;

    case Internal = 'internal';
    case Joint = 'joint';
}
