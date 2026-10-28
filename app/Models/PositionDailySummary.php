<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'day', 'positions_count', 'emission_hours', 'distance_nautical_miles', 'average_speed', 'zones'])]
class PositionDailySummary extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'day' => 'date',
            'emission_hours' => 'float',
            'distance_nautical_miles' => 'float',
            'average_speed' => 'float',
            'zones' => 'array',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
