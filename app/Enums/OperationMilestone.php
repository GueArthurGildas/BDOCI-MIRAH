<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum OperationMilestone: string implements HasBadge
{
    use HasTranslatedLabel;

    case Opened = 'opened';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Opened => 'danger',
            self::Closed => 'success',
        };
    }
}
