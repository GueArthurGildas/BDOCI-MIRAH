<?php

namespace Modules\Movements\Notifications;

use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class MissedDeclarationsAlert extends Alert
{
    public function __construct(private readonly int $count) {}

    public function key(): string
    {
        return 'movements.missed_declarations';
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Danger;
    }

    protected function icon(): string
    {
        return 'icon-alert-triangle';
    }

    protected function translationKey(): string
    {
        return 'movements::alerts.missed_declarations';
    }

    protected function params(): array
    {
        return ['count' => $this->count];
    }

    protected function target(): array
    {
        return ['movements.eez.missed.index', []];
    }
}
