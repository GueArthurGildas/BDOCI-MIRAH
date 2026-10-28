<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;

enum MessageTrigger: string
{
    use HasCommunicationsLabel;

    case User = 'user';
    case Rule = 'rule';
    case AiAgent = 'ai_agent';
}
