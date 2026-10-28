<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum EezDeclarationType: string implements HasBadge
{
    use HasTranslatedLabel;

    case Entry = 'entry';
    case Exit = 'exit';

    public function color(): string
    {
        return match ($this) {
            self::Entry => 'success',
            self::Exit => 'info',
        };
    }
}
