<?php

namespace App\Support\Connectors;

use App\Enums\DataSourceIndicator;

final class IndicatorRule
{
    public static function evaluate(bool $failed, int $rejectedCount, bool $isStale): DataSourceIndicator
    {
        return match (true) {
            $failed => DataSourceIndicator::Red,
            $isStale, $rejectedCount > 0 => DataSourceIndicator::Orange,
            default => DataSourceIndicator::Green,
        };
    }

    public static function staleAfterHours(): int
    {
        return (int) config('surveillance.connectors.stale_after_hours');
    }
}
