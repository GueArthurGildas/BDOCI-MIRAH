<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum VesselStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Active = 'active';
    case Suspended = 'suspended';
    case Inactive = 'inactive';

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Suspended => 'warning',
            self::Inactive => 'default',
        };
    }
}
