<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum ObservationLevel: string implements HasBadge
{
    use HasTranslatedLabel;

    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';

    public function color(): string
    {
        return match ($this) {
            self::Info => 'info',
            self::Warning => 'warning',
            self::Critical => 'danger',
        };
    }
}
