<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum ObservedUnitLevel: string implements HasBadge
{
    use HasTranslatedLabel;

    case Sighted = 'sighted';
    case Approached = 'approached';
    case Inspected = 'inspected';

    public function color(): string
    {
        return match ($this) {
            self::Sighted => 'info',
            self::Approached => 'warning',
            self::Inspected => 'success',
        };
    }
}
