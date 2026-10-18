<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum MissionStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::Planned => 'info',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'default',
        };
    }
}
