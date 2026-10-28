<?php

namespace Modules\Communications\Exceptions;

use RuntimeException;

class RecipientUnknown extends RuntimeException
{
    public function __construct(public readonly string $recipient)
    {
        parent::__construct("No active user for {$recipient}");
    }
}
