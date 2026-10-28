<?php

namespace App\Models;

use App\Enums\InfractionCaseStatus;
use App\Enums\InfractionQualification;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'reference', 'vessel_id', 'captain_id', 'shipowner_id', 'operation_id', 'inspection_id', 'mission_id', 'alert_id', 'witness_report_id',
    'qualification', 'facts', 'captain_observations', 'status', 'opened_by', 'opened_at',
])]
class InfractionCase extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'qualification' => InfractionQualification::class,
            'status' => InfractionCaseStatus::class,
            'opened_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'captain_id');
    }

    public function shipowner(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class, 'shipowner_id');
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(ExternalInspection::class, 'inspection_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function witnessReport(): BelongsTo
    {
        return $this->belongsTo(WitnessReport::class);
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function upheld(): HasMany
    {
        return $this->hasMany(UpheldInfraction::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(EvidenceItem::class);
    }

    public function seals(): HasMany
    {
        return $this->hasMany(Seal::class);
    }

    public function measures(): HasMany
    {
        return $this->hasMany(PrecautionaryMeasure::class);
    }

    public function seizures(): HasMany
    {
        return $this->hasMany(Seizure::class);
    }

    public function violationReport(): HasOne
    {
        return $this->hasOne(ViolationReport::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LitigationFollowUp::class);
    }

    public function suspensions(): HasMany
    {
        return $this->hasMany(LicenseSuspension::class);
    }

    public function revocations(): HasMany
    {
        return $this->hasMany(LicenseRevocation::class);
    }

    public function scopeNotClosed(Builder $query): void
    {
        $query->where('status', '!=', InfractionCaseStatus::Closed->value);
    }

    public function isClosed(): bool
    {
        return $this->status === InfractionCaseStatus::Closed;
    }
}
