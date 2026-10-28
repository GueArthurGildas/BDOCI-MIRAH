<?php

namespace App\Models;

use App\Enums\AlertRuleType;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'type', 'threshold_hours', 'parameters', 'is_active'])]
class AlertRule extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'type' => AlertRuleType::class,
            'threshold_hours' => 'float',
            'parameters' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
