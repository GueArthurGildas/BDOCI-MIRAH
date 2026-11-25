<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['rotation_id', 'logbook_day_id', 'started_at', 'handed_over_at', 'handed_over_by', 'taken_over_by', 'supervised_by', 'supervisor_name', 'operation_counters', 'open_operations', 'instructions'])]
class ShiftHandover extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'handed_over_at' => 'datetime',
            'operation_counters' => 'array',
            'open_operations' => 'array',
        ];
    }

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(Rotation::class, 'rotation_id');
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(LogbookDay::class, 'logbook_day_id');
    }

    public function outgoing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }

    public function incoming(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_over_by');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervised_by');
    }

    public function relays(): HasMany
    {
        return $this->hasMany(Relay::class, 'shift_handover_id');
    }
}
