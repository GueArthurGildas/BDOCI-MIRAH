<?php

namespace App\Support\Core;

use App\Models\Vessel;
use App\Models\VesselCurrentState;
use InvalidArgumentException;

class VesselStateUpdater
{
    private const UPDATABLE = [
        'license_status', 'license_expires_on', 'beacon_fault_open', 'last_position_at',
        'enhanced_monitoring', 'open_infractions', 'verification_count', 'traffic_light',
    ];

    /** @param array<string, mixed> $attributes */
    public function update(Vessel|int $vessel, array $attributes): VesselCurrentState
    {
        $unknown = array_diff(array_keys($attributes), self::UPDATABLE);

        throw_if($unknown !== [], InvalidArgumentException::class, 'Unknown vessel state attributes: '.implode(', ', $unknown));

        $vesselId = $vessel instanceof Vessel ? $vessel->getKey() : $vessel;

        $state = VesselCurrentState::firstOrNew(['vessel_id' => $vesselId]);
        $state->forceFill($attributes)->save();

        return $state;
    }
}
