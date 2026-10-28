<?php

namespace Modules\Licenses\Support;

use App\Enums\SuspensionReason;
use Illuminate\Support\Carbon;

final class SuspensionRules
{
    public static function maximumEnd(Carbon $startedOn): Carbon
    {
        return $startedOn->copy()->addMonthsNoOverflow((int) config('surveillance.license_suspension_max_months'));
    }

    public static function violation(SuspensionReason $reason, Carbon $startedOn, ?Carbon $plannedEndOn): ?string
    {
        if ($plannedEndOn === null) {
            return $reason === SuspensionReason::ForceMajeure ? null : 'planned_end_required';
        }

        if ($plannedEndOn->lt($startedOn)) {
            return 'end_before_start';
        }

        if ($reason !== SuspensionReason::ForceMajeure && $plannedEndOn->gt(self::maximumEnd($startedOn))) {
            return 'exceeds_maximum';
        }

        return null;
    }
}
