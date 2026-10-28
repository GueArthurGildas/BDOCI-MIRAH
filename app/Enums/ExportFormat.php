<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ExportFormat: string
{
    use HasTranslatedLabel;

    case Csv = 'csv';
    case Pdf = 'pdf';
}
