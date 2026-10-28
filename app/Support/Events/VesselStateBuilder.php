<?php

namespace App\Support\Events;

use App\Enums\CurrentLicenseStatus;
use App\Models\Vessel;
use App\Models\VesselCurrentState;

final class VesselStateBuilder
{
    public function refresh(int $vesselId): VesselCurrentState
    {
        $vessel = Vessel::withTrashed()->with(['currentLicense', 'latestPosition'])->withCount([
            'trackingDevices as installed_devices_count' => fn ($devices) => $devices->whereNull('removed_at'),
        ])->findOrFail($vesselId);

        $license = $vessel->currentLicense;
        $lastPositionAt = $vessel->latestPosition?->recorded_at;

        return VesselCurrentState::updateOrCreate(['vessel_id' => $vesselId], [
            'license_status' => CurrentLicenseStatus::fromLicense($license),
            'license_expires_on' => $license?->valid_until,
            'last_position_at' => $lastPositionAt,
            'vms_silence_hours' => $vessel->installed_devices_count > 0 && $lastPositionAt !== null
                ? round($lastPositionAt->diffInMinutes(now(), absolute: true) / 60, 2)
                : null,
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
