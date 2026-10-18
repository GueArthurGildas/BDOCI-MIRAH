<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum LicenseStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Valid = 'valid';
    case Expiring = 'expiring';
    case Expired = 'expired';
    case Suspended = 'suspended';

    public function color(): string
    {
        return match ($this) {
            self::Valid => 'success',
            self::Expiring => 'warning',
            self::Expired => 'danger',
            self::Suspended => 'default',
        };
    }
}
