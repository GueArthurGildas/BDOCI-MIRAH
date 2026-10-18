<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum VesselCategory: string implements HasBadge
{
    use HasTranslatedLabel;

    case Fishing = 'fishing';
    case Patrol = 'patrol';

    public function color(): string
    {
        return match ($this) {
            self::Fishing => 'info',
            self::Patrol => 'primary',
        };
    }
}
