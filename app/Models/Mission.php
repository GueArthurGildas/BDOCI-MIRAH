<?php

namespace App\Models;

use App\Enums\MissionNature;
use App\Enums\MissionStatus;
use App\Enums\MissionType;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[Fillable([
    'code', 'nature', 'type', 'origin', 'objectives', 'planned_start_at', 'planned_end_at', 'actual_start_at', 'actual_end_at',
    'zones', 'points_gps', 'status', 'operation_id', 'alert_id', 'witness_report_id', 'created_by',
])]
class Mission extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'nature' => MissionNature::class,
            'type' => MissionType::class,
            'status' => MissionStatus::class,
            'planned_start_at' => 'datetime',
            'planned_end_at' => 'datetime',
            'actual_start_at' => 'datetime',
            'actual_end_at' => 'datetime',
            'zones' => 'array',
            'points_gps' => 'array',
        ];
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function witnessReport(): BelongsTo
    {
        return $this->belongsTo(WitnessReport::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function authorizations(): HasMany
    {
        return $this->hasMany(MissionAuthorization::class)->latest('authorized_at');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MissionParticipant::class);
    }

    public function observerBoardings(): HasMany
    {
        return $this->hasMany(ObserverBoarding::class);
    }

    public function missionAssets(): HasMany
    {
        return $this->hasMany(MissionAsset::class);
    }

    public function briefing(): HasOne
    {
        return $this->hasOne(MissionBriefing::class);
    }

    public function preparation(): HasOne
    {
        return $this->hasOne(MissionPreparation::class);
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(PatrolTrack::class);
    }

    public function observedUnits(): HasMany
    {
        return $this->hasMany(ObservedUnit::class)->orderBy('observed_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MissionEvent::class)->orderBy('occurred_at');
    }

    public function debriefing(): HasOne
    {
        return $this->hasOne(MissionDebriefing::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(MissionReview::class);
    }

    public function infractionCases(): HasMany
    {
        return $this->hasMany(InfractionCase::class);
    }

    public function startsAt(): ?Carbon
    {
        return $this->actual_start_at ?? $this->planned_start_at;
    }
}
