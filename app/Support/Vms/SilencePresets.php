<?php

namespace App\Support\Vms;

final class SilencePresets
{
    public const HOURS = [
        '2_days' => 48,
        '3_days' => 72,
        '4_days' => 96,
        '5_days' => 120,
        '1_week' => 168,
        '2_weeks' => 336,
        '1_month' => 720,
    ];

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(array_keys(self::HOURS))->mapWithKeys(fn (string $key) => [$key => __('ui.silence_presets.'.$key)])->all();
    }

    public static function hours(string $key): int
    {
        return self::HOURS[$key];
    }
}
