<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum LogbookHandlingStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case ToHandle = 'to_handle';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::ToHandle => 'danger',
            self::InProgress => 'warning',
            self::Closed => 'success',
        };
    }
}
