<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum InspectionType: string
{
    use HasTranslatedLabel;

    case AtSea = 'at_sea';
    case AtPort = 'at_port';
    case Documentary = 'documentary';
}
