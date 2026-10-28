<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;

enum MessageForm: string
{
    use HasCommunicationsLabel;

    case Form03Csp04 = 'form_03_csp_04';
    case Form04Csp04 = 'form_04_csp_04';
    case None = 'none';
}
