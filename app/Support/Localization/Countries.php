<?php

namespace App\Support\Localization;

use Locale;

final class Countries
{
    public static function name(string $code): string
    {
        return Locale::getDisplayRegion("-{$code}", app()->getLocale());
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(config('surveillance.flag_states'))
            ->mapWithKeys(fn (string $code) => [$code => self::name($code)])
            ->sort()
            ->all();
    }
}
