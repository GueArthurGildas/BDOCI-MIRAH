<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum RejectionStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Pending = 'pending';
    case Corrected = 'corrected';
    case Dismissed = 'dismissed';

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Corrected => 'success',
            self::Dismissed => 'default',
        };
    }
}
