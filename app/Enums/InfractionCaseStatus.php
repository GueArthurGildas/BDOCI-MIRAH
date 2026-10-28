<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum InfractionCaseStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Open = 'open';
    case Instruction = 'instruction';
    case Decision = 'decision';
    case Closed = 'closed';

    public function color(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::Instruction => 'warning',
            self::Decision => 'danger',
            self::Closed => 'success',
        };
    }
}
