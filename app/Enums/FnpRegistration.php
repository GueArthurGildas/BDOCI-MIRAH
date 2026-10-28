<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum FnpRegistration: string implements HasBadge
{
    use HasTranslatedLabel;

    case Registered = 'registered';
    case Deregistered = 'deregistered';

    public function color(): string
    {
        return match ($this) {
            self::Registered => 'success',
            self::Deregistered => 'danger',
        };
    }
}
