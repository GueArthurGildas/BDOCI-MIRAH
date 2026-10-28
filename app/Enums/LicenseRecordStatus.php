<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum LicenseRecordStatus: string
{
    use HasTranslatedLabel;

    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
