<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum EezEntryOrigin: string implements HasBadge
{
    use HasTranslatedLabel;

    case Manual = 'manual';
    case Scraping = 'scraping';

    public function color(): string
    {
        return match ($this) {
            self::Manual => 'secondary',
            self::Scraping => 'info',
        };
    }
}
