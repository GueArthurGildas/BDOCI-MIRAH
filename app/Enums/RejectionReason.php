<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum RejectionReason: string
{
    use HasTranslatedLabel;

    case MissingField = 'missing_field';
    case InvalidValue = 'invalid_value';
    case DuplicateRecord = 'duplicate_record';
    case UnknownVessel = 'unknown_vessel';
    case OutOfZone = 'out_of_zone';
}
