<?php

namespace App\Models;

use App\Enums\VmsFailureCategory;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[Fillable(['vessel_id', 'category', 'started_at', 'ended_at', 'report_frequency_h', 'repair_deadline_days', 'operation_id'])]
class VmsFailure extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'category' => VmsFailureCategory::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'report_frequency_h' => 'integer',
            'repair_deadline_days' => 'integer',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function manualReports(): HasMany
    {
        return $this->hasMany(ManualPositionReport::class);
    }

    public function attestation(): HasOne
    {
        return $this->hasOne(WorkingOrderAttestation::class);
    }

    public function cessationOrder(): HasOne
    {
        return $this->hasOne(CessationOrder::class);
    }

    public function scopeOngoing(Builder $query): void
    {
        $query->whereNull('ended_at');
    }

    public function repairDeadlineAt(): Carbon
    {
        return $this->started_at->copy()->addDays($this->repair_deadline_days);
    }

    public function isOverdue(): bool
    {
        return $this->ended_at === null && $this->repairDeadlineAt()->isPast();
    }
}
