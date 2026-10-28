<?php

namespace Modules\Licenses\Notifications;

use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class LicensesExpiringAlert extends Alert
{
    public function __construct(private readonly int $count, private readonly ?int $days = null, private readonly int $from = 0) {}

    public function key(): string
    {
        return 'licenses.expiring.'.$this->days();
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Warning;
    }

    protected function icon(): string
    {
        return 'icon-file-text';
    }

    protected function translationKey(): string
    {
        return 'licenses::alerts.expiring';
    }

    protected function params(): array
    {
        return ['count' => $this->count, 'days' => $this->days()];
    }

    protected function target(): array
    {
        return ['licenses.index', ['expiring_after' => $this->from, 'expiring_within' => $this->days()]];
    }

    private function days(): int
    {
        return $this->days ?? (int) config('surveillance.license_expiry_warning_days');
    }
}
