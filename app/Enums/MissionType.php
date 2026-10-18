<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum MissionType: string
{
    use HasTranslatedLabel;

    case SeaPatrol = 'sea_patrol';
    case PortControl = 'port_control';
    case JointOperation = 'joint_operation';
}
