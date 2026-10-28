<?php

namespace App\Models;

use App\Enums\MrepPurpose;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'external_id', 'vessel_id', 'number', 'port_id', 'planned_arrival_at', 'purpose', 'last_port_call', 'catches_on_board', 'catches_to_land', 'is_complete', 'is_72h_deadline_met', 'raw', 'read_at',
])]
class MrepDpep extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'planned_arrival_at' => 'datetime',
            'purpose' => MrepPurpose::class,
            'catches_on_board' => 'array',
            'catches_to_land' => 'array',
            'is_complete' => 'boolean',
            'is_72h_deadline_met' => 'boolean',
            'raw' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }

    public function entryDecisions(): HasMany
    {
        return $this->hasMany(MrepEntryDecision::class, 'dpep_id');
    }

    public function portCalls(): HasMany
    {
        return $this->hasMany(MrepPortCall::class, 'dpep_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(MrepNotification::class, 'dpep_id');
    }

    public function latestDecision(): HasOne
    {
        return $this->hasOne(MrepEntryDecision::class, 'dpep_id')->latestOfMany('decided_at');
    }
}
