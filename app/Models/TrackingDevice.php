<?php

namespace App\Models;

use App\Enums\TrackingSystem;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['vessel_id', 'system', 'identifier', 'installed_at', 'removed_at'])]
class TrackingDevice extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'system' => TrackingSystem::class,
            'installed_at' => 'date',
            'removed_at' => 'date',
        ];
    }

    public function isInstalled(): bool
    {
        return $this->removed_at === null;
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function latestPosition(): HasOne
    {
        return $this->hasOne(Position::class)->latestOfMany('recorded_at');
    }
}
