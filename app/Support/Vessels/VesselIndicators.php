<?php

namespace App\Support\Vessels;

use App\Enums\AlertRuleType;
use App\Enums\MessageStatus;
use App\Models\Alert;
use App\Models\DeclaredObservedCatch;
use App\Models\ExternalInspection;
use App\Models\Inspection;
use App\Models\SentMessage;
use App\Models\Vessel;
use App\Queries\OpenInfractionCases;

final readonly class VesselIndicators
{
    public function __construct(
        public StatusLightFacts $facts,
        public ?float $silenceHours,
        public int $silences12Months,
        public ?int $daysBeforeExpiry,
        public int $inspections12Months,
        public int $infractions12Months,
        public int $checks,
        public ?float $meanCatchVariancePercent,
        public int $unansweredMessages,
        public bool $positionKnown,
    ) {}

    public static function for(Vessel $vessel): self
    {
        $since = now()->subMonths((int) config('vigilance.score.window_months'));
        $facts = StatusLightFacts::gather($vessel);

        return new self(
            facts: $facts,
            silenceHours: $vessel->currentState?->vms_silence_hours,
            silences12Months: Alert::where('vessel_id', $vessel->id)->where('type', AlertRuleType::Silence->value)->where('triggered_at', '>=', $since)->count(),
            daysBeforeExpiry: $facts->licenseDaysLeft,
            inspections12Months: ExternalInspection::where('vessel_id', $vessel->id)->where('started_at', '>=', $since)->count()
                + Inspection::where('vessel_id', $vessel->id)->where('inspected_at', '>=', $since)->count(),
            infractions12Months: OpenInfractionCases::countSince($vessel->id, $since),
            checks: $facts->checksCount,
            meanCatchVariancePercent: self::meanVariance($vessel->id, $since),
            unansweredMessages: SentMessage::where('vessel_id', $vessel->id)
                ->where('status', MessageStatus::Sent->value)
                ->where('reply_received', false)
                ->where('sent_at', '>=', $since)
                ->count(),
            positionKnown: $vessel->latestPosition !== null,
        );
    }

    private static function meanVariance(int $vesselId, \DateTimeInterface $since): ?float
    {
        $variances = DeclaredObservedCatch::query()
            ->whereNotNull('variance_pct')
            ->whereHas('inspection', fn ($inspections) => $inspections->where('vessel_id', $vesselId)->where('started_at', '>=', $since))
            ->pluck('variance_pct');

        return $variances->isEmpty() ? null : round($variances->map(fn ($value) => abs((float) $value))->avg(), 1);
    }
}
