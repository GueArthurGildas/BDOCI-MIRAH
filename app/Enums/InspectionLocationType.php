<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum InspectionLocationType: string
{
    use HasTranslatedLabel;

    case Port = 'port';
    case Sea = 'sea';
}
