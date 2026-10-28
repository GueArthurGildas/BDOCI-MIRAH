<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum VmsFailureCategory: string
{
    use HasTranslatedLabel;

    case Foreign = 'foreign';
    case Ivorian = 'ivorian';
    case Chartered = 'chartered';
    case TunaVessel = 'tuna_vessel';
}
