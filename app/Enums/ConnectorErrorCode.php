<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatedLabel;

enum ConnectorErrorCode: string
{
    use HasTranslatedLabel;

    case Timeout = 'timeout';
    case ConnectionRefused = 'connection_refused';
    case Unauthorized = 'unauthorized';
    case InvalidResponse = 'invalid_response';
    case ParsingFailed = 'parsing_failed';
}
