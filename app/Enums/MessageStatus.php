<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;
use App\Support\Presentation\HasBadge;

enum MessageStatus: string implements HasBadge
{
    use HasCommunicationsLabel;

    case Prepared = 'prepared';
    case Sent = 'sent';
    case Acknowledged = 'acknowledged';
    case Bounced = 'bounced';
    case Failed = 'failed';

    public function color(): string
    {
        return match ($this) {
            self::Prepared => 'default',
            self::Sent => 'info',
            self::Acknowledged => 'success',
            self::Bounced => 'warning',
            self::Failed => 'danger',
        };
    }
}
