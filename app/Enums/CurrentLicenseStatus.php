<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Models\License;
use App\Support\Presentation\HasBadge;

enum CurrentLicenseStatus: string implements HasBadge
{
    use HasTranslatedLabel;

    case NotProvided = 'not_provided';
    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';
    case Revoked = 'revoked';

    public static function fromLicense(?License $license): self
    {
        return match ($license?->status) {
            null => self::NotProvided,
            LicenseStatus::Valid, LicenseStatus::Expiring => self::Active,
            LicenseStatus::Suspended => self::Suspended,
            LicenseStatus::Expired => self::Expired,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::NotProvided => 'default',
            self::Suspended => 'warning',
            self::Expired, self::Revoked => 'danger',
        };
    }
}
