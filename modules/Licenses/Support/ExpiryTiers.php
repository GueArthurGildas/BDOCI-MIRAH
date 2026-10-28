<?php

namespace Modules\Licenses\Support;

use App\Models\License;
use Illuminate\Support\Collection;

final class ExpiryTiers
{
    /** @return Collection<int, array{from: int, days: int}> */
    public static function bands(): Collection
    {
        $days = collect([
            config('surveillance.license_expiry_warning_days'),
            config('surveillance.license_expiry_second_warning_days'),
            config('surveillance.license_expiry_third_warning_days'),
        ])->map(fn ($value) => (int) $value)->unique()->sort()->values();

        return $days->map(fn (int $tier, int $index) => ['from' => $index === 0 ? 0 : $days[$index - 1] + 1, 'days' => $tier]);
    }

    /** @return Collection<int, array{from: int, days: int, count: int}> */
    public static function counts(): Collection
    {
        return self::bands()->map(fn (array $band) => [...$band, 'count' => License::expiringBetween($band['from'], $band['days'])->count()]);
    }
}
