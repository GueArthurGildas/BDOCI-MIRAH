<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum CaptainSignatureStatus: string
{
    use HasTranslatedLabel;

    case Signed = 'signed';
    case Refused = 'refused';
    case Absent = 'absent';
}
