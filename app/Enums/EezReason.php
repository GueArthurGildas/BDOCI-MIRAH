<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum EezReason: string implements HasBadge
{
    use HasTranslatedLabel;

    case EezTransit = 'eez_transit';
    case EezFishing = 'eez_fishing';
    case IvorianPortTransit = 'ivorian_port_transit';
    case HighSeasFishing = 'high_seas_fishing';
    case ForeignEezFishing = 'foreign_eez_fishing';

    public function color(): string
    {
        return match ($this) {
            self::EezTransit => 'info',
            self::EezFishing => 'warning',
            self::IvorianPortTransit => 'info',
            self::HighSeasFishing => 'warning',
            self::ForeignEezFishing => 'warning',
        };
    }
}
