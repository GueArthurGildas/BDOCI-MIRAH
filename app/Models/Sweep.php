<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['control_slot_id', 'operator_user_id', 'vessels_count', 'non_emitting_count'])]
class Sweep extends Model
{
    use HasFactory;

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ControlSlot::class, 'control_slot_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_user_id');
    }

    public function anomalies(): HasMany
    {
        return $this->hasMany(SweepAnomaly::class);
    }
}
