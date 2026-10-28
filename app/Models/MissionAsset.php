<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mission_id', 'asset_id', 'engine_hours', 'nautical_miles', 'planned_fuel_l', 'consumed_fuel_l', 'state', 'breakdown', 'budget'])]
class MissionAsset extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'engine_hours' => 'decimal:1',
            'nautical_miles' => 'decimal:1',
            'planned_fuel_l' => 'decimal:1',
            'consumed_fuel_l' => 'decimal:1',
            'budget' => 'decimal:2',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
