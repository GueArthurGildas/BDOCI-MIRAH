<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum InfractionQualification: string implements HasBadge
{
    use HasTranslatedLabel;

    case Presumed = 'presumed';
    case Established = 'established';

    public function color(): string
    {
        return match ($this) {
            self::Presumed => 'warning',
            self::Established => 'danger',
        };
    }
}
