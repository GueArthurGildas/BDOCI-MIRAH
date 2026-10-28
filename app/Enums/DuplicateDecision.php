<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum DuplicateDecision: string
{
    use HasTranslatedLabel;

    case Merge = 'merge';
    case KeepSeparate = 'keep_separate';
    case Reject = 'reject';
}
