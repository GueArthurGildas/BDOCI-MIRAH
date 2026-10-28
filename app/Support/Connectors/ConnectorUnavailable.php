<?php

namespace App\Support\Connectors;

use App\Enums\ConnectorErrorCode;
use RuntimeException;

class ConnectorUnavailable extends RuntimeException
{
    public function __construct(public readonly ConnectorErrorCode $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
