<?php

namespace Modules\Vessels\Support;

use App\Models\Vessel;
use Illuminate\Support\Collection;

final class VesselFolderTree
{
    public const DEFAULT_PANE = 'identity';

    private const ITEMS_LIMIT = 10;

    private const FOLDERS = [
        'identification' => ['icon' => 'icon-user', 'leaves' => ['identity', 'photos', 'identifiers', 'actors', 'sources']],
        'licensing' => ['icon' => 'icon-file-text', 'leaves' => ['licenses', 'documents']],
        'tracking' => ['icon' => 'icon-radio', 'leaves' => ['position', 'devices', 'alerts', 'movements', 'eez', 'watch']],
        'controls' => ['icon' => 'icon-clipboard', 'leaves' => ['inspections', 'infractions', 'observations']],
        'activity' => ['icon' => 'icon-anchor', 'leaves' => ['catches', 'missions']],
        'follow_up' => ['icon' => 'icon-activity', 'leaves' => ['indicators', 'communications', 'comments', 'timeline']],
    ];

    private const ALWAYS_AVAILABLE = ['identity', 'photos', 'position'];

    /**
     * @param  array<int, string>  $tabs
     * @return array<int, array{key: string, icon: string, count: int, leaves: array<int, array<string, mixed>>}>
     */
    public static function build(Vessel $vessel, array $tabs, Collection $photos): array
    {
        $available = self::availableLeaves($tabs);

        return collect(self::FOLDERS)
            ->map(fn (array $folder, string $key) => [
                'key' => $key,
                'icon' => $folder['icon'],
                'leaves' => collect($folder['leaves'])
                    ->filter(fn (string $leaf) => in_array($leaf, $available, true))
                    ->map(fn (string $leaf) => self::leaf($vessel, $leaf, $photos))
                    ->values()
                    ->all(),
            ])
            ->map(fn (array $folder) => [...$folder, 'count' => array_sum(array_column($folder['leaves'], 'count'))])
            ->filter(fn (array $folder) => $folder['leaves'] !== [])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $tabs
     * @return array<int, string>
     */
    public static function availableLeaves(array $tabs): array
    {
        return array_values(array_unique([...$tabs, ...self::ALWAYS_AVAILABLE]));
    }

    /** @return array<string, mixed> */
    private static function leaf(Vessel $vessel, string $key, Collection $photos): array
    {
        [$collection, $label] = match ($key) {
            'photos' => [$photos, null],
            'identifiers' => [$vessel->identifiers, null],
            'actors' => [$vessel->actors, null],
            'sources' => [$vessel->sources, null],
            'licenses' => [$vessel->licenses, fn ($license) => $license->number],
            'documents' => [$vessel->licenseDocuments, null],
            'devices' => [$vessel->trackingDevices, fn ($device) => $device->identifier],
            'alerts' => [$vessel->alerts, null],
            'movements' => [$vessel->movements, null],
            'eez' => [$vessel->eezDeclarations, null],
            'watch' => [$vessel->watchEntries, null],
            'inspections' => [$vessel->externalInspections, fn ($inspection) => trim($inspection->started_at?->isoFormat('L').' '.($inspection->port?->name ?? ''))],
            'infractions' => [$vessel->infractionCases, fn ($case) => $case->reference],
            'observations' => [$vessel->observations, null],
            'catches' => [$vessel->catchDeclarations, null],
            'missions' => [$vessel->missions, fn ($mission) => $mission->code],
            'communications' => [$vessel->sentMessages->concat($vessel->internalNotes), null],
            'comments' => [$vessel->comments, null],
            default => [null, null],
        };

        return [
            'key' => $key,
            'count' => $collection?->count() ?? 0,
            'counted' => $collection !== null,
            'items' => $label === null ? [] : $collection->take(self::ITEMS_LIMIT)->map(fn ($item) => ['id' => $item->getKey(), 'label' => $label($item)])->all(),
        ];
    }
}
