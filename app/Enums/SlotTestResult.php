<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum SlotTestResult: string implements HasBadge
{
    use HasTranslatedLabel;

    case Ok = 'ok';
    case Ko = 'ko';
    case ToConfirm = 'to_confirm';

    public function color(): string
    {
        return match ($this) {
            self::Ok => 'success',
            self::Ko => 'danger',
            self::ToConfirm => 'warning',
        };
    }
}
