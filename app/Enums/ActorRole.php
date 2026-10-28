<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ActorRole: string
{
    use HasTranslatedLabel;

    case Owner = 'owner';
    case Shipowner = 'shipowner';
    case Beneficiary = 'beneficiary';
    case LocalAgent = 'local_agent';
    case Captain = 'captain';
    case FishingCaptain = 'fishing_captain';
}
