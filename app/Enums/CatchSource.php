<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum CatchSource: string
{
    use HasTranslatedLabel;

    case Logbook = 'logbook';
    case Landing = 'landing';
    case Observer = 'observer';
}
