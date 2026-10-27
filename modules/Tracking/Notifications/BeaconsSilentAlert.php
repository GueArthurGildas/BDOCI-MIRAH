<?php

namespace Modules\Tracking\Notifications;

use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class BeaconsSilentAlert extends Alert
{
    public function __construct(private readonly int $count) {}

    public function key(): string
    {
        return 'tracking.beacons-silent';
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Danger;
    }

    protected function icon(): string
    {
        return 'icon-radio';
    }

    protected function translationKey(): string
    {
        return 'tracking::alerts.beacons_silent';
    }

    protected function params(): array
    {
        return ['count' => $this->count, 'hours' => config('surveillance.beacon_silence_threshold_hours')];
    }

    protected function target(): array
    {
        return ['tracking.beacons', []];
    }
}
