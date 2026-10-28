<?php

namespace App\Models;

use App\Enums\MirrorOrigin;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'port_id', 'landed_at', 'origin', 'lines'])]
class Landing extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'landed_at' => 'datetime',
            'origin' => MirrorOrigin::class,
            'lines' => 'array',
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
}
