<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum LicenseType: string
{
    use HasTranslatedLabel;

    case Industrial = 'industrial';
    case Artisanal = 'artisanal';
}
