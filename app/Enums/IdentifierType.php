<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum IdentifierType: string
{
    use HasTranslatedLabel;

    case Imo = 'imo';
    case Mmsi = 'mmsi';
    case Ircs = 'ircs';
    case Registration = 'registration';
    case Iccat = 'iccat';
    case Eu = 'eu';
    case Beacon = 'beacon';
    case Other = 'other';
}
