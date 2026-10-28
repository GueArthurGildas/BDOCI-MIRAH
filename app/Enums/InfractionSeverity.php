<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum InfractionSeverity: string implements HasBadge
{
    use HasTranslatedLabel;

    case Minor = 'minor';
    case Serious = 'serious';
    case VerySerious = 'very_serious';

    public function color(): string
    {
        return match ($this) {
            self::Minor => 'info',
            self::Serious => 'warning',
            self::VerySerious => 'danger',
        };
    }
}
