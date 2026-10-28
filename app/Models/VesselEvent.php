<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'occurred_at', 'module', 'type', 'subject_type', 'subject_id', 'summary', 'zone_id', 'user_id', 'source'])]
class VesselEvent extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function scopeForModule(Builder $query, ?string $module): void
    {
        $query->when($module, fn (Builder $query) => $query->where('module', $module));
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
