<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['witness_report_id', 'rank', 'vessel_id', 'latitude', 'longitude', 'name', 'flag', 'registration_port', 'type', 'ircs', 'registration', 'other_marks', 'gear', 'is_fishing', 'route', 'is_drifting', 'is_resupplying', 'is_transshipping', 'description'])]
class ReportedVessel extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_fishing' => 'boolean',
            'is_drifting' => 'boolean',
            'is_resupplying' => 'boolean',
            'is_transshipping' => 'boolean',
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(WitnessReport::class, 'witness_report_id');
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
