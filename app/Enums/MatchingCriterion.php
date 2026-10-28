<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum MatchingCriterion: string
{
    use HasTranslatedLabel;

    case Imo = 'imo';
    case Mmsi = 'mmsi';
    case Ircs = 'ircs';
    case Registration = 'registration';
    case NameFlag = 'name_flag';
}
