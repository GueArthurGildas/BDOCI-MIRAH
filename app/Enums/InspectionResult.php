<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum InspectionResult: string implements HasBadge
{
    use HasTranslatedLabel;

    case Compliant = 'compliant';
    case MinorIssues = 'minor_issues';
    case NonCompliant = 'non_compliant';

    public function color(): string
    {
        return match ($this) {
            self::Compliant => 'success',
            self::MinorIssues => 'warning',
            self::NonCompliant => 'danger',
        };
    }
}
