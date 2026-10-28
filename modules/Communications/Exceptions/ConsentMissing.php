<?php

namespace Modules\Communications\Exceptions;

use RuntimeException;

class ConsentMissing extends RuntimeException
{
    public function __construct(public readonly string $recipient)
    {
        parent::__construct("No WhatsApp consent recorded for {$recipient}");
    }
}
