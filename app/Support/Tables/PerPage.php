<?php

namespace App\Support\Tables;

use Illuminate\Http\Request;

final class PerPage
{
    public const OPTIONS = [10, 20, 50, 100];

    public static function from(Request $request, int $default): int
    {
        $requested = $request->integer('per_page');

        return in_array($requested, self::OPTIONS, true) ? $requested : $default;
    }
}
