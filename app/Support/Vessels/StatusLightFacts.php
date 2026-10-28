<?php

namespace App\Support\Vessels;

use App\Enums\CurrentLicenseStatus;
use App\Enums\EezCheckResult;
use App\Enums\WatchOrigin;
use App\Enums\WatchStatus;
use App\Models\CheckCounter;
use App\Models\EezDeclarationCheck;
use App\Models\Vessel;
use App\Models\VmsFailure;
use App\Models\WatchEntry;
use App\Queries\OpenInfractionCases;
use App\Queries\SilentBeacons;

final readonly class StatusLightFacts
{
    public function __construct(
        public CurrentLicenseStatus $licenseStatus,
        public ?int $licenseDaysLeft,
        public bool $silent,
        public bool $failureOngoing,
        public bool $enhancedMonitoring,
        public int $openInfractions,
        public int $checksCount,
        public int $unexplainedVariances,
    ) {}

    public static function gather(Vessel $vessel): self
    {
        $license = $vessel->currentLicense;
        $failureOngoing = VmsFailure::where('vessel_id', $vessel->id)->whereNull('ended_at')->exists();

        return new self(
            licenseStatus: CurrentLicenseStatus::fromLicense($license),
            licenseDaysLeft: $license?->daysUntilExpiry(),
            silent: ! $failureOngoing && SilentBeacons::vessels($vessel->id)->isNotEmpty(),
            failureOngoing: $failureOngoing,
            enhancedMonitoring: WatchEntry::where('vessel_id', $vessel->id)
                ->where('status', WatchStatus::Open->value)
                ->where('origin', WatchOrigin::EnhancedMonitoring->value)
                ->exists(),
            openInfractions: OpenInfractionCases::countForVessel($vessel->id),
            checksCount: (int) CheckCounter::where('vessel_id', $vessel->id)->value('checks_count'),
            unexplainedVariances: self::unexplainedVariances($vessel->id, now()->subDays((int) config('vigilance.status_light.unexplained_variance_days'))),
        );
    }

    public static function unexplainedVariances(int $vesselId, \DateTimeInterface $since): int
    {
        return EezDeclarationCheck::query()
            ->where('result', EezCheckResult::Ko->value)
            ->whereHas('declaration', fn ($declarations) => $declarations->where('vessel_id', $vesselId)->where('received_at', '>=', $since))
            ->count();
    }
}
