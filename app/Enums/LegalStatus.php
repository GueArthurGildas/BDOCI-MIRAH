<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum LegalStatus: string
{
    use HasTranslatedLabel;

    case Ivorian = 'ivorian';
    case Chartered = 'chartered';
    case Foreign = 'foreign';
}
