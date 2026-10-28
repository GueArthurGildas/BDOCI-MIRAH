<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ReportFormat: string
{
    use HasTranslatedLabel;

    case Pdf = 'pdf';
    case Excel = 'excel';
}
