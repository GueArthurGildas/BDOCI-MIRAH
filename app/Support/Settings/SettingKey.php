<?php

namespace App\Support\Settings;

enum SettingKey: string
{
    case BeaconSilenceThresholdHours = 'beacon_silence_threshold_hours';
    case LicenseExpiryWarningDays = 'license_expiry_warning_days';
    case VerificationsBeforeSuspect = 'verifications_before_suspect';
    case CatchDiscrepancyThresholdPercent = 'catch_discrepancy_threshold_percent';
    case ShiftStart = 'shift_start';
    case ShiftEnd = 'shift_end';

    public function label(): string
    {
        return "ui.settings.{$this->value}";
    }

    public function type(): string
    {
        return match ($this) {
            self::BeaconSilenceThresholdHours, self::LicenseExpiryWarningDays, self::VerificationsBeforeSuspect => 'integer',
            self::CatchDiscrepancyThresholdPercent => 'numeric',
            self::ShiftStart, self::ShiftEnd => 'time',
        };
    }

    public function default(): mixed
    {
        return config("surveillance.{$this->value}");
    }

    /** @return array<int, mixed> */
    public function rules(): array
    {
        return match ($this->type()) {
            'integer' => ['required', 'integer', 'min:1', 'max:3650'],
            'numeric' => ['required', 'numeric', 'min:0', 'max:100'],
            'time' => ['required', 'date_format:H:i'],
        };
    }
}
