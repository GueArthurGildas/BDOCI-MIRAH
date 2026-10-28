<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum SuspensionReason: string
{
    use HasTranslatedLabel;

    case ForceMajeure = 'force_majeure';
    case Stocks = 'stocks';
    case Art105 = 'art_105';
    case Art106 = 'art_106';
    case VmsOverdue = 'vms_overdue';
    case Other = 'other';
}
