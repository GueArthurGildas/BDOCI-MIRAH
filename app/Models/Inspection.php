<?php

namespace App\Models;

use App\Enums\InspectionResult;
use App\Enums\InspectionType;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'vessel_id', 'port_id', 'external_reference', 'type',
    'inspected_at', 'inspectors', 'result', 'findings',
])]
class Inspection extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'type' => InspectionType::class,
            'result' => InspectionResult::class,
            'inspected_at' => 'datetime',
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

    public function infractions(): HasMany
    {
        return $this->hasMany(Infraction::class);
    }
}
