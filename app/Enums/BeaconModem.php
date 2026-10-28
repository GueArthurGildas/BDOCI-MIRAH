<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum BeaconModem: string implements HasBadge
{
    use HasTranslatedLabel;

    case Gnss = 'gnss';
    case Satellite = 'satellite';
    case Gprs = 'gprs';

    public function color(): string
    {
        return match ($this) {
            self::Gnss => 'info',
            self::Satellite => 'primary',
            self::Gprs => 'default',
        };
    }
}
