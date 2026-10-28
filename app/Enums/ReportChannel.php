<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ReportChannel: string
{
    use HasTranslatedLabel;

    case Email = 'email';
    case Fax = 'fax';
    case Phone = 'phone';
    case Mail = 'mail';
    case Form = 'form';
}
