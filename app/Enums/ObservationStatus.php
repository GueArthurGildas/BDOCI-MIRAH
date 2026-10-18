<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum ObservationStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Open => 'danger',
            self::InProgress => 'warning',
            self::Closed => 'success',
        };
    }
}
