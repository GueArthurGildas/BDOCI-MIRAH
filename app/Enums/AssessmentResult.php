<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum AssessmentResult: string implements HasBadge
{
    use HasTranslatedLabel;

    case Compatible = 'compatible';
    case Incompatible = 'incompatible';
    case ToComplete = 'to_complete';

    public function color(): string
    {
        return match ($this) {
            self::Compatible => 'success',
            self::Incompatible => 'danger',
            self::ToComplete => 'warning',
        };
    }
}
