<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;
use App\Support\Presentation\HasBadge;

enum EezCheckType: string implements HasBadge
{
    use HasTranslatedLabel;

    case Vms = 'vms';
    case Ais = 'ais';
    case License = 'license';
    case DesignatedPort = 'designated_port';
    case Dpep72h = 'dpep_72h';
    case Access = 'access';
    case InnLists = 'inn_lists';
    case Consistency = 'consistency';

    public function color(): string
    {
        return match ($this) {
            self::Vms => 'info',
            self::Ais => 'info',
            self::License => 'info',
            self::DesignatedPort => 'info',
            self::Dpep72h => 'info',
            self::Access => 'info',
            self::InnLists => 'info',
            self::Consistency => 'info',
        };
    }
}
