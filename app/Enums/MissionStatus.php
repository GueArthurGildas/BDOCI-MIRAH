<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum MissionStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Scheduled = 'scheduled';
    case Authorized = 'authorized';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'info',
            self::Authorized => 'primary',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Closed => 'default',
        };
    }
}
