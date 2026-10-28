<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum EezDeclarationStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Received = 'received';
    case Verified = 'verified';
    case Acknowledged = 'acknowledged';
    case Variance = 'variance';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Received => 'secondary',
            self::Verified => 'info',
            self::Acknowledged => 'success',
            self::Variance => 'danger',
            self::Closed => 'secondary',
        };
    }
}
