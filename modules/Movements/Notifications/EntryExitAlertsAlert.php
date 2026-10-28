<?php

namespace Modules\Movements\Notifications;

use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class EntryExitAlertsAlert extends Alert
{
    public function __construct(private readonly int $count) {}

    public function key(): string
    {
        return 'movements.entry_exit_alerts';
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Warning;
    }

    protected function icon(): string
    {
        return 'icon-log-in';
    }

    protected function translationKey(): string
    {
        return 'movements::alerts.entry_exit';
    }

    protected function params(): array
    {
        return ['count' => $this->count];
    }

    protected function target(): array
    {
        return ['movements.eez.alerts', []];
    }
}
