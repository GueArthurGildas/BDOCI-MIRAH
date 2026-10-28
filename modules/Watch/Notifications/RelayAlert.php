<?php

namespace Modules\Watch\Notifications;

use App\Models\Relay;
use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class RelayAlert extends Alert
{
    public function __construct(private readonly Relay $relay, private readonly string $vesselName) {}

    public function key(): string
    {
        return 'watch.relay.'.$this->relay->id;
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Info;
    }

    protected function icon(): string
    {
        return 'icon-repeat';
    }

    protected function translationKey(): string
    {
        return 'watch::alerts.relay';
    }

    protected function params(): array
    {
        return ['count' => 1, 'vessel' => $this->vesselName];
    }

    protected function target(): array
    {
        return ['watch.board.index', []];
    }
}
