<?php

namespace App\Models;

use App\Enums\ReportDecisionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['witness_report_id', 'decision', 'decided_by', 'decided_at'])]
class ReportDecision extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'decision' => ReportDecisionType::class,
            'decided_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(WitnessReport::class, 'witness_report_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
