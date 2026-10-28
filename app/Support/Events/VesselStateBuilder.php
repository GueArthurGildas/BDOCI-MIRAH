<?php

namespace App\Support\Events;

use App\Enums\CurrentLicenseStatus;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Support\Vessels\StatusLight;
use App\Support\Vessels\StatusLightFacts;

final class VesselStateBuilder
{
    public function refresh(int $vesselId): VesselCurrentState
    {
        $vessel = Vessel::withTrashed()->with(['currentLicense', 'latestPosition'])->withCount([
            'trackingDevices as installed_devices_count' => fn ($devices) => $devices->whereNull('removed_at'),
        ])->findOrFail($vesselId);

        $license = $vessel->currentLicense;
        $lastPositionAt = $vessel->latestPosition?->recorded_at;

        $facts = StatusLightFacts::gather($vessel);

        return VesselCurrentState::updateOrCreate(['vessel_id' => $vesselId], [
            'license_status' => CurrentLicenseStatus::fromLicense($license),
            'license_expires_on' => $license?->expires_on,
            'last_position_at' => $lastPositionAt,
            'vms_silence_hours' => $vessel->installed_devices_count > 0 && $lastPositionAt !== null
                ? round($lastPositionAt->diffInMinutes(now(), absolute: true) / 60, 2)
                : null,
            'vms_failure_ongoing' => $facts->failureOngoing,
            'is_enhanced_monitoring' => $facts->enhancedMonitoring,
            'open_infractions_count' => $facts->openInfractions,
            'checks_count' => $facts->checksCount,
            'status_light' => StatusLight::assess($facts)->level->value,
        ]);
    }

    public function rebuildAll(): int
    {
        $count = 0;

        Vessel::withTrashed()->pluck('id')->each(function (int $vesselId) use (&$count) {
            $this->refresh($vesselId);
            $count++;
        });

        return $count;
    }
}
