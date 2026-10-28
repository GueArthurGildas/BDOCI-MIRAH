<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum VesselStatusLight: string implements HasBadge
{
    use HasTranslatedLabel;

    case Compliant = 'compliant';
    case ToWatch = 'to_watch';
    case Suspect = 'suspect';
    case InViolation = 'in_violation';

    public function color(): string
    {
        return match ($this) {
            self::Compliant => 'success',
            self::ToWatch => 'warning',
            self::Suspect => 'inverse',
            self::InViolation => 'danger',
        };
    }

    public function severity(): int
    {
        return match ($this) {
            self::Compliant => 0,
            self::ToWatch => 1,
            self::Suspect => 2,
            self::InViolation => 3,
        };
    }
}
