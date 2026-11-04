<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum AlertCategory: string implements HasBadge
{
    use HasTranslatedLabel;

    case Urgent = 'urgent';
    case Useful = 'useful';
    case Other = 'other';

    public function color(): string
    {
        return match ($this) {
            self::Urgent => 'danger',
            self::Useful => 'warning',
            self::Other => 'default',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Urgent => 'icon-alert-octagon',
            self::Useful => 'icon-star',
            self::Other => 'icon-bell',
        };
    }
}
