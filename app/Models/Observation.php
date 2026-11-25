<?php

namespace App\Models;

use App\Enums\ObservationLevel;
use App\Enums\ObservationStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'vessel_id', 'team_id', 'author_id', 'zone_id', 'observed_at', 'latitude',
    'longitude', 'description', 'level', 'status', 'follow_up', 'closed_at', 'logbook_entry_id', 'deleted_by', 'deletion_reason',
])]
class Observation extends Model
{
    use Audited, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'level' => ObservationLevel::class,
            'status' => ObservationStatus::class,
            'observed_at' => 'datetime',
            'closed_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function scopePending(Builder $query): void
    {
        $query->whereNot('status', ObservationStatus::Closed);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function logbookEntry(): BelongsTo
    {
        return $this->belongsTo(LogbookEntry::class, 'logbook_entry_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
