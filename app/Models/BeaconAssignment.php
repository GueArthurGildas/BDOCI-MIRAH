<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'beacon_id', 'started_on', 'ended_on', 'replacement_reason'])]
class BeaconAssignment extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function beacon(): BelongsTo
    {
        return $this->belongsTo(Beacon::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('ended_on');
    }
}
