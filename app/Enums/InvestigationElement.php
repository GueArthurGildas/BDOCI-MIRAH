<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum InvestigationElement: string
{
    use HasTranslatedLabel;

    case Photos = 'photos';
    case Themis = 'themis';
    case GlobalRecord = 'global_record';
    case RfmoLists = 'rfmo_lists';
    case Trajectories = 'trajectories';
    case VmsExtract = 'vms_extract';
    case Press = 'press';
}
