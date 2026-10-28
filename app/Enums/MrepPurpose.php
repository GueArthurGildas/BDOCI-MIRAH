<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum MrepPurpose: string
{
    use HasTranslatedLabel;

    case Landing = 'lan';
    case Transshipment = 'trx';
    case Processing = 'pro';
    case Other = 'other';
}
