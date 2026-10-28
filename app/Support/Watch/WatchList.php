<?php

namespace App\Support\Watch;

use App\Enums\WatchOrigin;
use App\Enums\WatchStatus;
use App\Models\LogbookEntry;
use App\Models\Operation;
use App\Models\User;
use App\Models\Vessel;
use App\Models\WatchEntry;
use Illuminate\Support\Carbon;

final class WatchList
{
    public function open(
        Vessel|int $vessel,
        WatchOrigin $origin,
        ?User $decidedBy = null,
        ?LogbookEntry $entry = null,
        ?Operation $operation = null,
        ?Carbon $startedAt = null,
    ): WatchEntry {
        $vesselId = $vessel instanceof Vessel ? $vessel->id : $vessel;

        return WatchEntry::open()->firstOrCreate(
            ['vessel_id' => $vesselId, 'origin' => $origin, 'logbook_entry_id' => $entry?->id],
            [
                'started_at' => $startedAt ?? now(),
                'status' => WatchStatus::Open,
                'operation_id' => $operation?->id ?? $entry?->operation_id,
                'decided_by' => $decidedBy?->id,
            ],
        );
    }

    public function close(WatchEntry $entry): WatchEntry
    {
        if ($entry->status === WatchStatus::Open) {
            $entry->update(['status' => WatchStatus::Closed, 'closed_at' => now()]);
        }

        return $entry;
    }

    public function closeForLogbookEntry(LogbookEntry $entry): int
    {
        return $this->closeAll(WatchEntry::open()->where('logbook_entry_id', $entry->id)->get());
    }

    public function closeForOperation(Operation $operation): int
    {
        return $this->closeAll(WatchEntry::open()->where('operation_id', $operation->id)->get());
    }

    private function closeAll($entries): int
    {
        return $entries->each(fn (WatchEntry $entry) => $this->close($entry))->count();
    }
}
