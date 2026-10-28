<?php

namespace App\Support\Connectors;

final class CatchVariance
{
    public static function percent(?float $declared, ?float $observed): ?float
    {
        if ($declared === null || $observed === null || $declared <= 0.0) {
            return null;
        }

        return round(($observed - $declared) / $declared * 100, 2);
    }

    public static function exceedsThreshold(?float $variancePercent): bool
    {
        return $variancePercent !== null && abs($variancePercent) > (float) config('surveillance.connectors.catch_variance_threshold_percent');
    }
}
