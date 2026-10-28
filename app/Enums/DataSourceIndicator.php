<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum DataSourceIndicator: string implements HasBadge
{
    use HasTranslatedLabel;

    case Green = 'green';
    case Orange = 'orange';
    case Red = 'red';

    public function color(): string
    {
        return match ($this) {
            self::Green => 'success',
            self::Orange => 'warning',
            self::Red => 'danger',
        };
    }
}
