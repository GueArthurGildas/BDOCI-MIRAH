<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum FollowUpType: string
{
    use HasTranslatedLabel;

    case Warning = 'warning';
    case AdministrativePenalty = 'administrative_penalty';
    case Settlement = 'settlement';
    case ForwardToHierarchy = 'forward_to_hierarchy';
    case ForwardToCourt = 'forward_to_court';
}
