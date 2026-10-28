<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reference_list_id', 'code', 'label', 'sort_order', 'is_active'])]
class ReferenceValue extends Model
{
    protected function casts(): array
    {
        return [
            'label' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function translatedLabel(?string $locale = null): string
    {
        $labels = $this->label;

        return $labels[$locale ?? app()->getLocale()]
            ?? $labels[config('app.fallback_locale')]
            ?? (string) (reset($labels) ?: $this->code);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(ReferenceList::class, 'reference_list_id');
    }
}
