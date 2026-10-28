<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;
use App\Support\Presentation\HasBadge;

enum NoteUrgency: string implements HasBadge
{
    use HasCommunicationsLabel;

    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';

    public function color(): string
    {
        return match ($this) {
            self::Low => 'default',
            self::Normal => 'info',
            self::High => 'danger',
        };
    }
}
