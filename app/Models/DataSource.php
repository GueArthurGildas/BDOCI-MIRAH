<?php

namespace App\Models;

use App\Enums\DataSourceIndicator;
use App\Enums\DataSourceMode;
use App\Enums\RejectionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['code', 'name', 'mode', 'indicator_status', 'last_synced_at', 'last_error', 'is_stale'])]
class DataSource extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'mode' => DataSourceMode::class,
            'indicator_status' => DataSourceIndicator::class,
            'last_synced_at' => 'datetime',
            'is_stale' => 'boolean',
        ];
    }

    public function syncs(): HasMany
    {
        return $this->hasMany(DataSourceSync::class)->latest('started_at');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(DataSourceError::class)->latest('occurred_at');
    }

    public function rejections(): HasMany
    {
        return $this->hasMany(DataSourceRejection::class)->latest('received_at');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'object')->latest('id');
    }

    public function scopeWithIssueCounts(Builder $query): void
    {
        $query->withCount([
            'errors as open_errors_count' => fn (Builder $errors) => $errors->whereNull('resolved_at'),
            'rejections as pending_rejections_count' => fn (Builder $rejections) => $rejections->where('status', RejectionStatus::Pending),
            'comments',
        ]);
    }
}
