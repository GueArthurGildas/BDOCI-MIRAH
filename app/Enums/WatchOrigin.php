<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum WatchOrigin: string
{
    use HasTranslatedLabel;

    case DirectorListThemis = 'director_list_themis';
    case DirectorListManual = 'director_list_manual';
    case Logbook = 'logbook';
    case EezEntry = 'eez_entry';
    case EezExit = 'eez_exit';
    case EnhancedMonitoring = 'enhanced_monitoring';
    case MoreThan3Checks = 'more_than_3_checks';
}
