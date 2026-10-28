<?php

namespace App\Models;

use App\Enums\ObservedUnitLevel;
use App\Enums\SightingSource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mission_id', 'vessel_id', 'level', 'sighting_source', 'recorded_name', 'recorded_marks', 'observed_activity', 'latitude', 'longitude', 'observed_at', 'inspection_id'])]
class ObservedUnit extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'level' => ObservedUnitLevel::class,
            'sighting_source' => SightingSource::class,
            'observed_at' => 'datetime',
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(ExternalInspection::class, 'inspection_id');
    }
}
