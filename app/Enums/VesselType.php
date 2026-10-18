<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum VesselType: string
{
    use HasTranslatedLabel;

    case Trawler = 'trawler';
    case Seiner = 'seiner';
    case Longliner = 'longliner';
    case Canoe = 'canoe';
    case PatrolBoat = 'patrol_boat';
    case Support = 'support';
}
