<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum SweepAnomalyType: string
{
    use HasTranslatedLabel;

    case NonEmitting = 'non_emitting';
    case LicensedAbsentThemis = 'licensed_absent_themis';
    case Other = 'other';
}
