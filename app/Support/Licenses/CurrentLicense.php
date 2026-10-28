<?php

namespace App\Support\Licenses;

use App\Models\License;
use Illuminate\Support\Collection;

final class CurrentLicense
{
    public const ORDER_COLUMN = 'expires_on';

    public const ORDER_AGGREGATE = 'max';

    /** @param Collection<int, License> $licenses */
    public static function select(Collection $licenses): ?License
    {
        return $licenses->sortByDesc(fn (License $license) => $license->{self::ORDER_COLUMN})->first();
    }
}
