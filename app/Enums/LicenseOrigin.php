<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum LicenseOrigin: string
{
    use HasTranslatedLabel;

    case Import = 'import';
    case Batch = 'batch';
    case Manual = 'manual';
}
