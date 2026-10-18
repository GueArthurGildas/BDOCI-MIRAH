<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum TrackingSystem: string implements HasBadge
{
    use HasTranslatedLabel;

    case Vms = 'vms';
    case Ais = 'ais';

    public function color(): string
    {
        return match ($this) {
            self::Vms => 'primary',
            self::Ais => 'info',
        };
    }
}
