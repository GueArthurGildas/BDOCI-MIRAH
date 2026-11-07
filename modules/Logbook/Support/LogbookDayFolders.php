<?php

namespace Modules\Logbook\Support;

use App\Models\LogbookDay;
use App\Models\LogbookEntry;
use App\Models\Vessel;
use Illuminate\Support\Collection;

final class LogbookDayFolders
{
    public const FOLDERS = ['summary', 'entries', 'vessels', 'actors', 'operations', 'decisions'];

    /**
     * @return array{
     *     entries: Collection<int, LogbookEntry>,
     *     channels: Collection<int, array{id: int, label: string, count: int}>,
     *     vessels: Collection<int, array{id: int, label: string, count: int, model: Vessel}>,
     *     actors: Collection<int, array{id: int, label: string, count: int}>,
     *     operations: Collection<int, array{id: int, label: string, count: int}>,
     *     decisions: int,
     *     filtered: Collection<int, LogbookEntry>
     * }
     */
    public static function for(LogbookDay $day, string $folder = 'summary', ?int $item = null): array
    {
        $day->loadMissing(['entries.channel', 'entries.vessels', 'entries.operation', 'entries.author']);
        $entries = $day->entries->sortBy('occurred_at')->values();

        return [
            'entries' => $entries,
            'channels' => $entries->groupBy('channel_id')->map(fn (Collection $group, $id) => ['id' => (int) $id, 'label' => $group->first()->channel->label, 'count' => $group->count()])->sortBy('label')->values(),
            'vessels' => $entries->flatMap(fn (LogbookEntry $entry) => $entry->vessels)->groupBy('id')->map(fn (Collection $group, $id) => ['id' => (int) $id, 'label' => $group->first()->name, 'count' => $group->count(), 'model' => $group->first()])->sortBy('label')->values(),
            'actors' => $entries->filter(fn (LogbookEntry $entry) => $entry->author)->groupBy('entered_by')->map(fn (Collection $group, $id) => ['id' => (int) $id, 'label' => $group->first()->author->fullName(), 'count' => $group->count()])->sortBy('label')->values(),
            'operations' => $entries->filter(fn (LogbookEntry $entry) => $entry->operation)->groupBy('operation_id')->map(fn (Collection $group, $id) => ['id' => (int) $id, 'label' => $group->first()->operation->code, 'count' => $group->count()])->sortBy('label')->values(),
            'decisions' => $entries->where('is_order_or_decision', true)->count(),
            'filtered' => self::filter($entries, $folder, $item),
        ];
    }

    /**
     * @param  Collection<int, LogbookEntry>  $entries
     * @return Collection<int, LogbookEntry>
     */
    private static function filter(Collection $entries, string $folder, ?int $item): Collection
    {
        return match ($folder) {
            'entries' => $item ? $entries->where('channel_id', $item)->values() : $entries,
            'vessels' => $item ? $entries->filter(fn (LogbookEntry $entry) => $entry->vessels->contains('id', $item))->values() : $entries->filter(fn (LogbookEntry $entry) => $entry->vessels->isNotEmpty())->values(),
            'actors' => $item ? $entries->where('entered_by', $item)->values() : $entries,
            'operations' => $item ? $entries->where('operation_id', $item)->values() : $entries->whereNotNull('operation_id')->values(),
            'decisions' => $entries->where('is_order_or_decision', true)->values(),
            default => $entries,
        };
    }
}
