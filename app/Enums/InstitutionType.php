<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum InstitutionType: string
{
    use HasTranslatedLabel;

    case Rfmo = 'rfmo';
    case Rfb = 'rfb';
    case Fao = 'fao';
    case State = 'state';
    case Authority = 'authority';
    case Other = 'other';
}
