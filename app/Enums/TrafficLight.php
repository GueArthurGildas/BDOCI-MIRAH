<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum TrafficLight: string implements HasBadge
{
    use HasTranslatedLabel;

    case Compliant = 'compliant';
    case Watch = 'watch';
    case Suspect = 'suspect';
    case Infringing = 'infringing';

    public function color(): string
    {
        return match ($this) {
            self::Compliant => 'success',
            self::Watch => 'info',
            self::Suspect => 'warning',
            self::Infringing => 'danger',
        };
    }
}
