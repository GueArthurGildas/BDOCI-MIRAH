<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum LicenseImportStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case Preview = 'preview';
    case Valid = 'valid';
    case Cancelled = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::Preview => 'warning',
            self::Valid => 'success',
            self::Cancelled => 'default',
        };
    }
}
