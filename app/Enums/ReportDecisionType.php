<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ReportDecisionType: string
{
    use HasTranslatedLabel;

    case Dismissal = 'dismissal';
    case ForwardToInstruction = 'forward_to_instruction';
}
