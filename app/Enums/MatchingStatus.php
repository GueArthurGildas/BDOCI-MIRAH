<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum MatchingStatus: string
{
    use HasTranslatedLabel;

    case Matched = 'matched';
    case PossibleDuplicate = 'possible_duplicate';
    case New = 'new';
    case Rejected = 'rejected';
}
