<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ErsSource: string
{
    use HasTranslatedLabel;

    case Import = 'import';
    case Api = 'api';
    case ManualEntry = 'manual_entry';
}
