<?php

namespace App\Support\Vessels;

use App\Enums\ActorRole;
use App\Enums\IdentifierType;
use App\Models\LegalEntity;
use App\Models\Vessel;
use App\Models\VesselActor;
use App\Models\VesselHistory;
use App\Models\VesselIdentifier;

final class VesselRelationsSynchronizer
{
    public const IDENTIFIER_COLUMNS = [
        'registration_number' => IdentifierType::Registration,
        'imo_number' => IdentifierType::Imo,
        'call_sign' => IdentifierType::Ircs,
    ];

    public static function recordHistory(Vessel $vessel): void
    {
        $nameChanged = $vessel->isDirty('name');
        $flagChanged = $vessel->isDirty('flag_country_id') && $vessel->getOriginal('flag_country_id') !== null;

        if (! $nameChanged && ! $flagChanged) {
            return;
        }

        VesselHistory::create([
            'vessel_id' => $vessel->id,
            'former_name' => $nameChanged ? $vessel->getOriginal('name') : null,
            'former_flag_country_id' => $flagChanged ? $vessel->getOriginal('flag_country_id') : null,
            'changed_on' => today(),
        ]);
    }

    public static function sync(Vessel $vessel): void
    {
        self::syncIdentifiers($vessel);
        self::syncOwner($vessel);
    }

    public static function syncAll(Vessel $vessel): void
    {
        self::syncIdentifiers($vessel, force: true);
    }

    private static function syncIdentifiers(Vessel $vessel, bool $force = false): void
    {
        foreach (self::IDENTIFIER_COLUMNS as $column => $type) {
            if (! $force && ! $vessel->wasRecentlyCreated && ! $vessel->wasChanged($column)) {
                continue;
            }

            $value = $vessel->{$column};
            $active = VesselIdentifier::where('vessel_id', $vessel->id)->where('type', $type->value)->whereNull('valid_to')->get();

            if ($value !== null && $active->contains('value', $value)) {
                continue;
            }

            $active->each(fn (VesselIdentifier $identifier) => $identifier->update(['valid_to' => today()]));

            if (filled($value)) {
                VesselIdentifier::create([
                    'vessel_id' => $vessel->id,
                    'type' => $type,
                    'value' => $value,
                    'valid_from' => $active->isEmpty() ? null : today(),
                ]);
            }
        }
    }

    private static function syncOwner(Vessel $vessel): void
    {
        $name = $vessel->pendingOwner;
        $vessel->pendingOwner = null;
        $vessel->unsetRelation('currentOwner');

        if ($name === null) {
            return;
        }

        $current = VesselActor::with(['legalEntity', 'person'])->where('vessel_id', $vessel->id)->where('role', ActorRole::Owner->value)->whereNull('ended_on')->get();

        if (blank($name)) {
            $current->each(fn (VesselActor $actor) => $actor->update(['ended_on' => today()]));

            return;
        }

        if ($current->contains(fn (VesselActor $actor) => $actor->holderName() === $name)) {
            return;
        }

        $current->each(fn (VesselActor $actor) => $actor->update(['ended_on' => today()]));

        VesselActor::create([
            'vessel_id' => $vessel->id,
            'role' => ActorRole::Owner,
            'legal_entity_id' => LegalEntity::firstOrCreate(['name' => $name])->id,
            'started_on' => today(),
        ]);
    }
}
