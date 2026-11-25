<?php

namespace App\Support\Logbook;

use App\Enums\LogbookDayStatus;
use App\Models\LogbookEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class RecordDeletion
{
    public function ensureDeletable(?LogbookEntry $entry): void
    {
        if ($entry !== null && $entry->day?->status !== LogbookDayStatus::Open) {
            throw ValidationException::withMessages(['reason' => __('ui.deletion.day_closed')]);
        }
    }

    public function delete(Model $record, User $actor, string $reason): void
    {
        $record->forceFill(['deleted_by' => $actor->id, 'deletion_reason' => $reason])->save();
        $record->delete();
    }

    public function cancel(?LogbookEntry $entry, User $actor, string $reason): void
    {
        if ($entry === null || $entry->isCancelled()) {
            return;
        }

        $entry->update(['cancelled_at' => now(), 'cancelled_by' => $actor->id, 'cancellation_reason' => $reason]);
    }
}
