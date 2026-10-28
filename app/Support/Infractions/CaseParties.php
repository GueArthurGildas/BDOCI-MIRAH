<?php

namespace App\Support\Infractions;

use App\Enums\ActorRole;
use App\Models\Vessel;
use App\Models\VesselActor;

final class CaseParties
{
    private const CAPTAIN_ROLES = [ActorRole::Captain, ActorRole::FishingCaptain];

    private const SHIPOWNER_ROLES = [ActorRole::Owner, ActorRole::Shipowner];

    /** @return array{captain_id: ?int, shipowner_id: ?int} */
    public static function forVessel(Vessel $vessel): array
    {
        $current = VesselActor::query()
            ->where('vessel_id', $vessel->id)
            ->whereNull('ended_on')
            ->orderByDesc('started_on')
            ->orderByDesc('id')
            ->get();

        return [
            'captain_id' => $current->first(fn (VesselActor $actor) => in_array($actor->role, self::CAPTAIN_ROLES, true) && $actor->person_id !== null)?->person_id,
            'shipowner_id' => $current->first(fn (VesselActor $actor) => in_array($actor->role, self::SHIPOWNER_ROLES, true) && $actor->legal_entity_id !== null)?->legal_entity_id,
        ];
    }
}
