<?php

namespace App\Enums;

use App\Enums\Concerns\HasCommunicationsLabel;

enum DispatchMode: string
{
    use HasCommunicationsLabel;

    case Automatic = 'automatic';
    case WithValidation = 'with_validation';
}
