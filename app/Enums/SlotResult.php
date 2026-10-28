<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum SlotResult: string implements HasBadge
{
    use HasTranslatedLabel;

    case Normal = 'normal';
    case Suspect = 'suspect';

    public function color(): string
    {
        return match ($this) {
            self::Normal => 'success',
            self::Suspect => 'danger',
        };
    }
}
