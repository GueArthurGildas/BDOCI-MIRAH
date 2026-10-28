<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum SurveillanceObservationStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Monitoring = 'monitoring';
    case Closed = 'closed';
    case Operation = 'operation';

    public function color(): string
    {
        return match ($this) {
            self::Monitoring => 'warning',
            self::Closed => 'success',
            self::Operation => 'info',
        };
    }
}
