<?php

namespace Modules\Communications\Support;

use App\Models\Contact;
use App\Models\SentMessage;
use Illuminate\Support\Collection;

final readonly class NotificationResult
{
    /**
     * @param  Collection<int, SentMessage>  $messages
     * @param  array<int, array{contact: Contact, reason: string}>  $skipped
     */
    public function __construct(public Collection $messages, public array $skipped) {}
}
