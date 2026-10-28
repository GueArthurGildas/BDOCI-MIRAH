<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum FnpDeregistrationReason: string
{
    use HasTranslatedLabel;

    case Destruction = 'destruction';
    case UnauthorizedModification = 'unauthorized_modification';
    case FlagChange = 'flag_change';
    case InnList = 'inn_list';
    case FiveYearsWithoutLicense = 'five_years_without_license';
}
