<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'detected_at', 'reminders', 'qualification'])]
class MissedDeclaration extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'reminders' => 'integer',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function scopeUnresolved(Builder $query): void
    {
        $query->whereNotExists(fn ($subquery) => $subquery
            ->selectRaw('1')
            ->from('eez_declarations')
            ->whereColumn('eez_declarations.vessel_id', 'missed_declarations.vessel_id')
            ->where('eez_declarations.type', 'entry')
            ->whereColumn('eez_declarations.received_at', '>=', 'missed_declarations.detected_at'));
    }

    public function isResolved(): bool
    {
        return EezDeclaration::where('vessel_id', $this->vessel_id)
            ->where('type', 'entry')
            ->where('received_at', '>=', $this->detected_at)
            ->exists();
    }
}
