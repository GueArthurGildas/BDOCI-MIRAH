<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum WitnessReportStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Received = 'received';
    case Verified = 'verified';
    case Closed = 'closed';
    case Forwarded = 'forwarded';

    public function color(): string
    {
        return match ($this) {
            self::Received => 'info',
            self::Verified => 'warning',
            self::Closed => 'default',
            self::Forwarded => 'success',
        };
    }
}
