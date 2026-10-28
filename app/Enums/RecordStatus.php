<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum RecordStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Draft = 'draft';
    case ToMatch = 'to_match';
    case Validated = 'validated';

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'default',
            self::ToMatch => 'warning',
            self::Validated => 'success',
        };
    }
}
