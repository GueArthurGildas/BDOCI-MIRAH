<?php

namespace App\Support\Vessels;

use App\Enums\SourceSystem;
use App\Models\SourcePriority;
use App\Models\Vessel;
use App\Models\VesselFieldProvenance;

class SourcePriorityResolver
{
    private const UNRANKED = 99;

    /** @var array<string, int>|null */
    private ?array $overrides = null;

    public function rank(string $field, SourceSystem $source): int
    {
        $this->overrides ??= SourcePriority::all()
            ->mapWithKeys(fn (SourcePriority $priority) => ["{$priority->field}|{$priority->source->value}" => $priority->rank])
            ->all();

        if (isset($this->overrides["{$field}|{$source->value}"])) {
            return $this->overrides["{$field}|{$source->value}"];
        }

        $position = array_search($source->value, config('registry.default_source_order'), true);

        return $position === false ? self::UNRANKED : $position + 1;
    }

    public function forget(): void
    {
        $this->overrides = null;
    }

    public function mayOverride(Vessel $vessel, string $field, SourceSystem $incoming): bool
    {
        $current = VesselFieldProvenance::where('vessel_id', $vessel->id)->where('field', $field)->first();

        if ($vessel->getAttribute($field) === null) {
            return true;
        }

        return $this->rank($field, $incoming) <= $this->rank($field, $current?->source ?? SourceSystem::ManualEntry);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<int, string>
     */
    public function apply(Vessel $vessel, array $values, SourceSystem $source): array
    {
        $applied = [];

        foreach (config('registry.tracked_vessel_fields') as $field) {
            if (! array_key_exists($field, $values) || blank($values[$field])) {
                continue;
            }

            $current = $vessel->getAttribute($field);
            $incoming = $values[$field];

            if ($current !== null && (string) ($current instanceof \BackedEnum ? $current->value : $current) === (string) ($incoming instanceof \BackedEnum ? $incoming->value : $incoming)) {
                $this->recordProvenance($vessel, $field, $source, $current === null);

                continue;
            }

            if (! $this->mayOverride($vessel, $field, $source)) {
                continue;
            }

            $vessel->setAttribute($field, $incoming);
            $applied[] = $field;
        }

        $vessel->save();

        foreach ($applied as $field) {
            $this->recordProvenance($vessel, $field, $source, true);
        }

        return $applied;
    }

    public function recordProvenance(Vessel $vessel, string $field, SourceSystem $source, bool $overwrite): void
    {
        if (! $overwrite && VesselFieldProvenance::where('vessel_id', $vessel->id)->where('field', $field)->exists()) {
            return;
        }

        VesselFieldProvenance::upsert(
            [['vessel_id' => $vessel->id, 'field' => $field, 'source' => $source->value, 'updated_at' => now()]],
            ['vessel_id', 'field'],
            ['source', 'updated_at'],
        );
    }
}
