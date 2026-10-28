<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum MissionType: string
{
    use HasTranslatedLabel;

    case Maritime = 'maritime';
    case Lagoon = 'lagoon';
    case Continental = 'continental';
    case Port = 'port';
    case AerialDrone = 'aerial_drone';
}
