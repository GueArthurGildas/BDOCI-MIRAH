<?php

namespace Modules\Communications\Notifications;

use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class PreparedMessagesAlert extends Alert
{
    public function __construct(private readonly int $count) {}

    public function key(): string
    {
        return 'communications.prepared_messages';
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Warning;
    }

    protected function icon(): string
    {
        return 'icon-send';
    }

    protected function translationKey(): string
    {
        return 'communications::alerts.prepared_messages';
    }

    protected function params(): array
    {
        return ['count' => $this->count];
    }

    protected function target(): array
    {
        return ['communications.messages.index', ['status' => 'prepared']];
    }
}
