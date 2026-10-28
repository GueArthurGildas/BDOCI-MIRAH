<?php

namespace App\Models;

use App\Enums\PrecautionaryMeasureType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['infraction_case_id', 'type', 'proposed_by', 'decided_by', 'decided_at'])]
class PrecautionaryMeasure extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => PrecautionaryMeasureType::class,
            'decided_at' => 'datetime',
        ];
    }

    public function infractionCase(): BelongsTo
    {
        return $this->belongsTo(InfractionCase::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isDecided(): bool
    {
        return $this->decided_at !== null;
    }
}
