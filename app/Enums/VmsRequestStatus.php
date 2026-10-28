<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum VmsRequestStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Requested = 'requested';
    case Assessed = 'assessed';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Requested => 'warning',
            self::Assessed => 'info',
            self::Closed => 'success',
        };
    }
}
