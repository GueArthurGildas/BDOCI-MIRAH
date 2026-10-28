<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum EezCheckResult: string implements HasBadge
{
    use HasTranslatedLabel;

    case Ok = 'ok';
    case Ko = 'ko';
    case NotApplicable = 'na';

    public function color(): string
    {
        return match ($this) {
            self::Ok => 'success',
            self::Ko => 'danger',
            self::NotApplicable => 'secondary',
        };
    }
}
