<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum MissionChecklistItem: string
{
    use HasTranslatedLabel;

    case Fuel = 'fuel';
    case Radio = 'radio';
    case Gps = 'gps';
    case InspectionKit = 'inspection_kit';
    case Seals = 'seals';
    case Safety = 'safety';
    case Documents = 'documents';
}
