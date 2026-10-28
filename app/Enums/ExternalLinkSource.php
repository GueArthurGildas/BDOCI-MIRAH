<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ExternalLinkSource: string
{
    use HasTranslatedLabel;

    case Themis = 'themis';
    case Neno = 'neno';
    case EInspection = 'e_inspection';
}
