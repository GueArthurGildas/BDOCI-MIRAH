<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum BeaconStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Emitting = 'emitting';
    case Silent = 'silent';
    case NoData = 'no_data';

    public function color(): string
    {
        return match ($this) {
            self::Emitting => 'success',
            self::Silent => 'danger',
            self::NoData => 'default',
        };
    }
}
