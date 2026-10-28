<?php

namespace App\Support\Vms;

use App\Enums\TrackingSystem;
use App\Models\Beacon;
use App\Models\BeaconAssignment;
use App\Models\TrackingDevice;

final class BeaconMirror
{
    public function project(int $vesselId): void
    {
        BeaconAssignment::with('beacon')->where('vessel_id', $vesselId)->get()->each(function (BeaconAssignment $assignment) {
            TrackingDevice::updateOrCreate(
                ['system' => TrackingSystem::Vms, 'identifier' => $assignment->beacon->serial_number],
                ['vessel_id' => $assignment->vessel_id, 'installed_at' => $assignment->started_on, 'removed_at' => $assignment->ended_on],
            );
        });
    }

    public function importLegacyDevices(): int
    {
        $imported = 0;

        TrackingDevice::where('system', TrackingSystem::Vms)->get()->each(function (TrackingDevice $device) use (&$imported) {
            if (Beacon::where('serial_number', $device->identifier)->exists()) {
                return;
            }

            $beacon = Beacon::create(['serial_number' => $device->identifier]);

            BeaconAssignment::create([
                'vessel_id' => $device->vessel_id,
                'beacon_id' => $beacon->id,
                'started_on' => $device->installed_at,
                'ended_on' => $device->removed_at,
            ]);

            $imported++;
        });

        return $imported;
    }
}
