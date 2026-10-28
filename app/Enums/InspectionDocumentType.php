<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum InspectionDocumentType: string
{
    use HasTranslatedLabel;

    case License = 'license';
    case Certificate = 'certificate';
    case FishingLog = 'fishing_log';
    case HoldPlan = 'hold_plan';
    case Other = 'other';
}
