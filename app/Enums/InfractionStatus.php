<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum InfractionStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Reported = 'reported';
    case UnderInvestigation = 'under_investigation';
    case Sanctioned = 'sanctioned';
    case Closed = 'closed';
    case Dismissed = 'dismissed';

    public function color(): string
    {
        return match ($this) {
            self::Reported => 'info',
            self::UnderInvestigation => 'warning',
            self::Sanctioned => 'danger',
            self::Closed => 'success',
            self::Dismissed => 'default',
        };
    }
}
