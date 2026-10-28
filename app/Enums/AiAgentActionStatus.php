<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;
use App\Support\Presentation\HasBadge;

enum AiAgentActionStatus: string implements HasBadge
{
    use HasCommunicationsLabel;

    case Requested = 'requested';
    case Executed = 'executed';
    case Refused = 'refused';
    case Failed = 'failed';

    public function color(): string
    {
        return match ($this) {
            self::Requested => 'info',
            self::Executed => 'success',
            self::Refused => 'warning',
            self::Failed => 'danger',
        };
    }
}
