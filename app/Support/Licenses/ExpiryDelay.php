<?php

namespace App\Support\Licenses;

use Carbon\CarbonInterface;

final class ExpiryDelay
{
    private const PARTS = 2;

    /** @return array{days: int, expired: bool, label: string} */
    public static function describe(CarbonInterface $expiresOn, ?CarbonInterface $today = null): array
    {
        $from = ($today ?? today())->copy()->startOfDay();
        $to = $expiresOn->copy()->startOfDay();
        $days = (int) $from->diffInDays($to, false);

        if ($days === 0) {
            return ['days' => 0, 'expired' => false, 'label' => __('licenses::licenses.expiry.today')];
        }

        $interval = $from->diffForHumans($to, ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => self::PARTS, 'join' => true, 'skip' => ['week']]);

        return [
            'days' => $days,
            'expired' => $days < 0,
            'label' => __($days < 0 ? 'licenses::licenses.expiry.since' : 'licenses::licenses.expiry.in', ['interval' => $interval]),
        ];
    }
}
