<?php

namespace App\Support\Connectors;

use App\Enums\IdentifierType;
use App\Models\Vessel;
use App\Models\VesselIdentifier;

final class VesselLocator
{
    private const COLUMNS = [
        'imo' => 'imo_number',
        'ircs' => 'call_sign',
        'registration' => 'registration_number',
    ];

    /** @param array<string, mixed> $identifiers keys imo, mmsi, ircs, registration */
    public static function find(array $identifiers): ?Vessel
    {
        foreach (['imo', 'mmsi', 'ircs', 'registration'] as $type) {
            $value = $identifiers[$type] ?? null;

            if (blank($value)) {
                continue;
            }

            $vesselId = $type === 'mmsi'
                ? VesselIdentifier::where('type', IdentifierType::Mmsi->value)->where('value', $value)->whereNull('valid_to')->value('vessel_id')
                : Vessel::where(self::COLUMNS[$type], $value)->value('id');

            if ($vesselId !== null) {
                return Vessel::find($vesselId);
            }
        }

        return null;
    }
}
