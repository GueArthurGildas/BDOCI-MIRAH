<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum LicenseSegment: string
{
    use HasTranslatedLabel;

    case Artisanal = 'artisanal';
    case SemiIndustrial = 'semi_industrial';
    case Industrial = 'industrial';
}
