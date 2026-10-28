<?php

namespace App\Models;

use App\Enums\WatchOrigin;
use App\Enums\WatchStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'origin', 'started_at', 'status', 'closed_at', 'logbook_entry_id', 'operation_id', 'entry_exit_alert_id', 'decided_by'])]
class WatchEntry extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'origin' => WatchOrigin::class,
            'status' => WatchStatus::class,
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function scopeOpen(Builder $query): void
    {
        $query->where('status', WatchStatus::Open);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }

    public function logbookEntry(): BelongsTo
    {
        return $this->belongsTo(LogbookEntry::class, 'logbook_entry_id');
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
