<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum DataSourceStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Operational = 'operational';
    case Degraded = 'degraded';
    case Down = 'down';

    public function color(): string
    {
        return match ($this) {
            self::Operational => 'success',
            self::Degraded => 'warning',
            self::Down => 'danger',
        };
    }
}
