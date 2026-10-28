<?php

namespace App\Models;

use App\Enums\CaptainSignatureStatus;
use App\Enums\InspectionLocationType;
use App\Enums\InspectionResult;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'external_id', 'reference', 'vessel_id', 'location_type', 'port_id', 'latitude', 'longitude', 'started_at', 'ended_at', 'control_type', 'result', 'captain_signature_status', 'is_copy_handed_over', 'raw', 'read_at',
])]
class ExternalInspection extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'location_type' => InspectionLocationType::class,
            'captain_signature_status' => CaptainSignatureStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_copy_handed_over' => 'boolean',
            'raw' => 'array',
            'read_at' => 'datetime',
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
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

    public function documents(): HasMany
    {
        return $this->hasMany(InspectionDocument::class, 'inspection_id');
    }

    public function gears(): HasMany
    {
        return $this->hasMany(InspectionGear::class, 'inspection_id');
    }

    public function catches(): HasMany
    {
        return $this->hasMany(DeclaredObservedCatch::class, 'inspection_id');
    }

    public function infractions(): HasMany
    {
        return $this->hasMany(InspectionInfraction::class, 'inspection_id');
    }

    public function portCalls(): HasMany
    {
        return $this->hasMany(MrepPortCall::class, 'inspection_id');
    }

    public function resultCase(): ?InspectionResult
    {
        return InspectionResult::tryFrom((string) $this->result);
    }

    public function resultLabel(): string
    {
        return $this->resultCase() ? __($this->resultCase()->label()) : (string) ($this->result ?? '—');
    }
}
