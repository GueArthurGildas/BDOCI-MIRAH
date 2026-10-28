<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum AssetType: string
{
    use HasTranslatedLabel;

    case InterventionBoat = 'intervention_boat';
    case PatrolBoat = 'patrol_boat';
    case Vehicle = 'vehicle';
    case Drone = 'drone';
    case Vhf = 'vhf';
    case Gps = 'gps';
    case InspectionEquipment = 'inspection_equipment';
    case Seals = 'seals';
}
