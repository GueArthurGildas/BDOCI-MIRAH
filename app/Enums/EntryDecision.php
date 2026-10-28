<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum EntryDecision: string implements HasBadge
{
    use HasTranslatedLabel;

    case Authorization = 'authorization';
    case Refusal = 'refusal';
    case InspectionOnly = 'inspection_only';

    public function color(): string
    {
        return match ($this) {
            self::Authorization => 'success',
            self::Refusal => 'danger',
            self::InspectionOnly => 'warning',
        };
    }
}
