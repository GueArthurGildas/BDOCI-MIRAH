<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['rotation_id', 'started_at'])]
class ShiftHandover extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
        ];
    }

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(Rotation::class, 'rotation_id');
    }

    public function relays(): HasMany
    {
        return $this->hasMany(Relay::class, 'shift_handover_id');
    }
}
