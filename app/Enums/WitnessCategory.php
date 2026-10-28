<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum WitnessCategory: string
{
    use HasTranslatedLabel;

    case StateVessel = 'state_vessel';
    case Fisher = 'fisher';
    case Coaster = 'coaster';
    case PortAuthority = 'port_authority';
    case Other = 'other';
}
