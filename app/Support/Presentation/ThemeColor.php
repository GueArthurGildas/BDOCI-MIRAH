<?php

namespace App\Support\Presentation;

final class ThemeColor
{
    private const HEX_BY_NAME = [
        'primary' => '#01a9ac',
        'success' => '#0ac282',
        'warning' => '#fe9365',
        'danger' => '#fe5d70',
        'info' => '#2dcee3',
        'inverse' => '#404e67',
        'default' => '#bdc3c7',
    ];

    public static function hex(string $name): string
    {
        return self::HEX_BY_NAME[$name] ?? self::HEX_BY_NAME['default'];
    }
}
