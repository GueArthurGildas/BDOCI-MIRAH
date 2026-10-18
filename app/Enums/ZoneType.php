<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ZoneType: string
{
    use HasTranslatedLabel;

    case PortArea = 'port_area';
    case TerritorialSea = 'territorial_sea';
    case ExclusiveEconomicZone = 'exclusive_economic_zone';
    case Restricted = 'restricted';
}
