<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum WatchStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Open = 'open';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Open => 'danger',
            self::Closed => 'success',
        };
    }
}
