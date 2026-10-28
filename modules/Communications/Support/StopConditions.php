<?php

namespace Modules\Communications\Support;

use App\Models\DispatchRule;
use App\Models\Vessel;

class StopConditions
{
    public const ACKNOWLEDGED = 'acknowledged';

    public const REPLY_RECEIVED = 'reply_received';

    /** @return array<int, string> */
    public static function codes(): array
    {
        return [self::ACKNOWLEDGED, self::REPLY_RECEIVED];
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::codes())->mapWithKeys(fn (string $code) => [$code => __("communications::stop_conditions.{$code}")])->all();
    }

    public function isMet(DispatchRule $rule, Vessel $vessel): bool
    {
        $messages = $rule->messages()->where('vessel_id', $vessel->id);

        return match ($rule->stop_condition) {
            self::ACKNOWLEDGED => $messages->whereNotNull('acknowledged_at')->exists(),
            self::REPLY_RECEIVED => $messages->where('reply_received', true)->exists(),
            default => false,
        };
    }
}
