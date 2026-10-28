<?php

namespace App\Support\Connectors;

use App\Enums\RejectionReason;
use RuntimeException;

class RejectedRecord extends RuntimeException
{
    public function __construct(public readonly RejectionReason $reason, public readonly string $details)
    {
        parent::__construct($details);
    }
}
