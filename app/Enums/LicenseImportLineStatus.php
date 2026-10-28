<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum LicenseImportLineStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case ToValidate = 'to_validate';
    case Valid = 'valid';
    case Rejected = 'rejected';

    public function color(): string
    {
        return match ($this) {
            self::ToValidate => 'warning',
            self::Valid => 'success',
            self::Rejected => 'danger',
        };
    }
}
