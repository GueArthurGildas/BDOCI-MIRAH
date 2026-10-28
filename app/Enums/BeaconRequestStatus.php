<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum BeaconRequestStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Requested = 'requested';
    case Installed = 'installed';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Requested => 'warning',
            self::Installed => 'success',
            self::Closed => 'default',
        };
    }
}
