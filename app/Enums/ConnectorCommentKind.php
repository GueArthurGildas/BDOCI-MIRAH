<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum ConnectorCommentKind: string implements HasBadge
{
    use HasTranslatedLabel;

    case Note = 'note';
    case Status = 'status';
    case Action = 'action';

    public function color(): string
    {
        return match ($this) {
            self::Note => 'default',
            self::Status => 'info',
            self::Action => 'primary',
        };
    }
}
