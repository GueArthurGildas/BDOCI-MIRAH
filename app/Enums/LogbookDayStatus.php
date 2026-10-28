<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum LogbookDayStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Open = 'open';
    case Closed = 'closed';
    case Locked = 'locked';

    public function color(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Closed => 'info',
            self::Locked => 'success',
        };
    }
}
