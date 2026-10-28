<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum SourceSystem: string
{
    use HasTranslatedLabel;

    case ManualEntry = 'manual_entry';
    case Themis = 'themis';
    case Neno = 'neno';
    case EInspection = 'e_inspection';
    case Dpep = 'dpep';
    case Mrep = 'mrep';
}
