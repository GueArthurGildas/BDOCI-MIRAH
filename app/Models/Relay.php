<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['shift_handover_id', 'surveillance_observation_id', 'next_team_id', 'read_acknowledged_by', 'acknowledged_at'])]
class Relay extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
        ];
    }

    public function handover(): BelongsTo
    {
        return $this->belongsTo(ShiftHandover::class, 'shift_handover_id');
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(SurveillanceObservation::class, 'surveillance_observation_id');
    }

    public function nextTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'next_team_id');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_acknowledged_by');
    }
}
