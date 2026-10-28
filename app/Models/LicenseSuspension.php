<?php

namespace App\Models;

use App\Enums\SuspensionReason;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['license_id', 'reason', 'started_on', 'planned_end_on', 'lifted_on', 'infraction_case_id', 'vms_failure_id', 'operation_id', 'proposed_by', 'decided_by'])]
class LicenseSuspension extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'reason' => SuspensionReason::class,
            'started_on' => 'date',
            'planned_end_on' => 'date',
            'lifted_on' => 'date',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function scopeInForce(Builder $query): void
    {
        $query->whereNull('lifted_on');
    }

    public function isInForce(): bool
    {
        return $this->lifted_on === null;
    }

    public function isOverdue(): bool
    {
        return $this->isInForce() && $this->planned_end_on !== null && $this->planned_end_on->isBefore(today());
    }
}
