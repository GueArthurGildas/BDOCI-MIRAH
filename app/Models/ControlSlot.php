<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['started_at', 'ended_at', 'team_id', 'operator_user_id'])]
class ControlSlot extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_user_id');
    }

    public function slotPositions(): HasMany
    {
        return $this->hasMany(SlotPosition::class);
    }

    public function zoneCounts(): HasMany
    {
        return $this->hasMany(ZoneCount::class);
    }

    public function sweeps(): HasMany
    {
        return $this->hasMany(Sweep::class);
    }
}
