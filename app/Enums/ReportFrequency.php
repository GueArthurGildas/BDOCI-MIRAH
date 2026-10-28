<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ReportFrequency: string
{
    use HasTranslatedLabel;

    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
}
