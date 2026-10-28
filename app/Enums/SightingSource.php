<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum SightingSource: string
{
    use HasTranslatedLabel;

    case Visual = 'visual';
    case Radar = 'radar';
    case Vms = 'vms';
}
