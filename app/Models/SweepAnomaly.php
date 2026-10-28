<?php

namespace App\Models;

use App\Enums\SweepAnomalyType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sweep_id', 'vessel_id', 'type'])]
class SweepAnomaly extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => SweepAnomalyType::class,
        ];
    }

    public function sweep(): BelongsTo
    {
        return $this->belongsTo(Sweep::class);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
