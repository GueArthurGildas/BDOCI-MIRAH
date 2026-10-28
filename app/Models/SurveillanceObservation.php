<?php

namespace App\Models;

use App\Enums\SurveillanceObservationStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'slot_position_id', 'logbook_entry_id', 'body', 'status', 'created_by', 'created_at', 'legacy_observation_id'])]
class SurveillanceObservation extends Model
{
    use Audited, HasFactory;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'status' => SurveillanceObservationStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class, 'vessel_id');
    }

    public function logbookEntry(): BelongsTo
    {
        return $this->belongsTo(LogbookEntry::class, 'logbook_entry_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
