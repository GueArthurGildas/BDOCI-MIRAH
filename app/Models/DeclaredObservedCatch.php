<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'inspection_id', 'species_id', 'presentation_id', 'declared_weight_kg', 'observed_weight_kg', 'net_weight_kg', 'conversion_factor', 'variance_pct', 'exceeds_csp_threshold',
])]
class DeclaredObservedCatch extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'declared_weight_kg' => 'decimal:2',
            'observed_weight_kg' => 'decimal:2',
            'net_weight_kg' => 'decimal:2',
            'conversion_factor' => 'decimal:3',
            'variance_pct' => 'decimal:2',
            'exceeds_csp_threshold' => 'boolean',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(ExternalInspection::class, 'inspection_id');
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }
}
