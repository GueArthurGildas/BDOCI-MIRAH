<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum LogbookDirection: string implements HasBadge
{
    use HasTranslatedLabel;

    case Received = 'received';
    case Sent = 'sent';

    public function color(): string
    {
        return match ($this) {
            self::Received => 'info',
            self::Sent => 'primary',
        };
    }
}
