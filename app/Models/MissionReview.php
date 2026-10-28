<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mission_id', 'agents_count', 'duration_hours', 'nautical_miles', 'fuel_l', 'sighted_units_count', 'approached_units_count', 'inspected_units_count', 'compliant_count', 'non_compliant_count', 'infractions_count', 'violation_reports_count', 'diversions_count', 'immobilizations_count', 'seizures_count', 'arrests_count', 'penalties_count', 'calculated_at'])]
class MissionReview extends Model
{
    use HasFactory;

    protected $primaryKey = 'mission_id';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'duration_hours' => 'decimal:1',
            'nautical_miles' => 'decimal:1',
            'fuel_l' => 'decimal:1',
            'calculated_at' => 'datetime',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
