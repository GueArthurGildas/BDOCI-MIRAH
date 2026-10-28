<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ExternalListType: string
{
    use HasTranslatedLabel;

    case GlobalRecord = 'global_record';
    case Rfmo = 'rfmo';
    case Inn = 'inn';
}
