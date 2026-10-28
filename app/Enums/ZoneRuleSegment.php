<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ZoneRuleSegment: string
{
    use HasTranslatedLabel;

    case Artisanal = 'artisanal';
    case SmallPelagics = 'small_pelagics';
    case Trawl = 'trawl';
    case TunaVessel = 'tuna_vessel';
}
