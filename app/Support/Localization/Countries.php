<?php

namespace App\Support\Localization;

use Locale;

final class Countries
{
    private const ISO3 = [
        'CI' => 'CIV', 'BJ' => 'BEN', 'BZ' => 'BLZ', 'CN' => 'CHN', 'ES' => 'ESP', 'FR' => 'FRA', 'GH' => 'GHA',
        'GN' => 'GIN', 'JP' => 'JPN', 'KR' => 'KOR', 'LR' => 'LBR', 'MA' => 'MAR', 'NG' => 'NGA', 'PA' => 'PAN',
        'PT' => 'PRT', 'SL' => 'SLE', 'SN' => 'SEN', 'TG' => 'TGO', 'TW' => 'TWN', 'ML' => 'MLI', 'BF' => 'BFA',
        'US' => 'USA', 'GB' => 'GBR', 'IT' => 'ITA', 'GR' => 'GRC', 'RU' => 'RUS', 'VN' => 'VNM', 'ID' => 'IDN',
    ];

    public static function name(string $code): string
    {
        return Locale::getDisplayRegion("-{$code}", app()->getLocale());
    }

    public static function iso3(string $iso2): string
    {
        return self::ISO3[strtoupper($iso2)] ?? strtoupper($iso2).'X';
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
