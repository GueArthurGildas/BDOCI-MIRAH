<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum FleetSegment: string
{
    use HasTranslatedLabel;

    case PatrolVessel = 'patrol_vessel';
    case Industrial = 'industrial';
    case SemiIndustrial = 'semi_industrial';
    case Artisanal = 'artisanal';
    case AuthorizedForeign = 'authorized_foreign';
    case KnownUnauthorized = 'known_unauthorized';
}
