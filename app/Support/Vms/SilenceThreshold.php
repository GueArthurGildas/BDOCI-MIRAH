<?php

namespace App\Support\Vms;

use App\Enums\AlertRuleType;
use App\Models\AlertRule;

final class SilenceThreshold
{
    private ?float $hours = null;

    public function hours(): float
    {
        return $this->hours ??= $this->resolve();
    }

    public function forget(): void
    {
        $this->hours = null;
    }

    private function resolve(): float
    {
        $rule = AlertRule::active()->where('type', AlertRuleType::Silence)->whereNotNull('threshold_hours')->orderBy('id')->first();

        return $rule?->threshold_hours ?? (float) config('surveillance.beacon_silence_threshold_hours');
    }
}
