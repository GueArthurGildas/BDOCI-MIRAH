<?php

namespace App\Models;

use App\Enums\MirrorOrigin;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'partner_vessel_id', 'transshipped_at', 'origin', 'lines'])]
class Transshipment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'transshipped_at' => 'datetime',
            'origin' => MirrorOrigin::class,
            'lines' => 'array',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Vessel::class, 'partner_vessel_id');
    }
}
