<?php

namespace Modules\Licenses\Notifications;

use App\Enums\LicenseStatus;
use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class LicensesExpiringAlert extends Alert
{
    public function __construct(private readonly int $count) {}

    public function key(): string
    {
        return 'licenses.expiring';
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
        return ['count' => $this->count, 'days' => config('surveillance.license_expiry_warning_days')];
    }

    protected function target(): array
    {
        return ['licenses.index', ['status' => LicenseStatus::Expiring->value]];
    }
}
