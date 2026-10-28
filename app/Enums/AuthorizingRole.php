<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum AuthorizingRole: string
{
    use HasTranslatedLabel;

    case Director = 'director';
    case TeamLead = 'team_lead';
}
