<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum AlertStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Open = 'open';
    case Acknowledged = 'acknowledged';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Open => 'danger',
            self::Acknowledged => 'warning',
            self::Closed => 'success',
        };
    }
}
