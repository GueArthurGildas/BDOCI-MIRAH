<?php

namespace App\Models;

use App\Enums\MovementDirection;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'port_id', 'recorded_by', 'direction', 'occurred_at', 'reason', 'notes'])]
class Movement extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'direction' => MovementDirection::class,
            'occurred_at' => 'datetime',
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

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
