<?php

namespace Modules\Vessels\Support;

use App\Enums\IdentifierType;
use App\Enums\RecordStatus;
use App\Enums\SourceSystem;
use App\Enums\VesselCategory;
use App\Enums\VesselStatus;
use App\Models\Country;
use App\Models\Vessel;
use App\Models\VesselIdentifier;
use App\Support\Vessels\SourcePriorityResolver;
use App\Support\Vessels\VesselRelationsSynchronizer;

class VesselRecordWriter
{
    public function __construct(private readonly SourcePriorityResolver $priorities) {}

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, string>
     */
    public function clashingIdentifiers(array $record): array
    {
        return collect($record['identifiers'] ?? [])
            ->filter(filled(...))
            ->filter(fn (string $value, string $type) => VesselIdentifier::where('type', $type)->where('value', $value)->whereNull('valid_to')->exists())
            ->all();
    }

    /** @param array<string, mixed> $record */
    public function create(array $record, SourceSystem $source, RecordStatus $status): Vessel
    {
        $identifiers = $this->withoutClashes($record);
        $columns = $this->identifierColumns($identifiers);
        $attributes = $record['attributes'] ?? [];

        $vessel = Vessel::create([
            'name' => $record['name'],
            'record_status' => $status,
            'creation_origin' => $source,
            'category' => ($attributes['fleet_segment'] ?? null) === 'patrol_vessel' ? VesselCategory::Patrol : VesselCategory::Fishing,
            'status' => VesselStatus::Active,
            'flag' => $record['flag'] ?? null,
            ...$columns,
        ]);

        $this->attachIdentifiers($vessel, $identifiers);
        $this->priorities->apply($vessel, $this->fieldValues($record), $source);
        $this->priorities->recordProvenance($vessel, 'name', $source, true);

        if (filled($record['flag'] ?? null)) {
            $this->priorities->recordProvenance($vessel, 'flag_country_id', $source, true);
        }

        return $vessel;
    }

    /** @param array<string, mixed> $record */
    public function enrich(Vessel $vessel, array $record, SourceSystem $source): void
    {
        $identifiers = $this->withoutClashesOf($vessel, $record);
        $columns = collect($this->identifierColumns($identifiers))->filter(fn ($value, $column) => $vessel->{$column} === null)->all();

        if ($columns !== []) {
            $vessel->update($columns);
        }

        $this->attachIdentifiers($vessel, $identifiers);
        $this->priorities->apply($vessel, [...$this->fieldValues($record), 'name' => $record['name'] ?? null], $source);
    }

    /** @param array<string, mixed> $record */
    public function fieldValues(array $record): array
    {
        $values = $record['attributes'] ?? [];

        if (filled($record['flag'] ?? null)) {
            $values['flag_country_id'] = Country::forIso2($record['flag'])->id;
        }

        return $values;
    }

    /**
     * @param  array<string, string>  $identifiers
     * @return array<string, string>
     */
    private function identifierColumns(array $identifiers): array
    {
        $columns = [];

        foreach (VesselRelationsSynchronizer::IDENTIFIER_COLUMNS as $column => $type) {
            if (isset($identifiers[$type->value])) {
                $columns[$column] = $identifiers[$type->value];
            }
        }

        return $columns;
    }

    /** @param array<string, string> $identifiers */
    private function attachIdentifiers(Vessel $vessel, array $identifiers): void
    {
        $synced = array_map(fn (IdentifierType $type) => $type->value, VesselRelationsSynchronizer::IDENTIFIER_COLUMNS);

        foreach ($identifiers as $type => $value) {
            if (in_array($type, $synced, true) || $vessel->activeIdentifiers()->where('type', $type)->exists()) {
                continue;
            }

            VesselIdentifier::create(['vessel_id' => $vessel->id, 'type' => $type, 'value' => $value]);
        }
    }

    /** @return array<string, string> */
    private function withoutClashes(array $record): array
    {
        return collect($record['identifiers'] ?? [])->filter(filled(...))->except(array_keys($this->clashingIdentifiers($record)))->all();
    }

    /** @return array<string, string> */
    private function withoutClashesOf(Vessel $vessel, array $record): array
    {
        $own = $vessel->activeIdentifiers()->pluck('value', 'type')->map(fn ($value) => $value);

        return collect($record['identifiers'] ?? [])
            ->filter(filled(...))
            ->filter(fn (string $value, string $type) => ! VesselIdentifier::where('type', $type)->where('value', $value)->whereNull('valid_to')->where('vessel_id', '!=', $vessel->id)->exists())
            ->reject(fn (string $value, string $type) => $own->has($type) && $own->get($type) !== $value)
            ->all();
    }
}
