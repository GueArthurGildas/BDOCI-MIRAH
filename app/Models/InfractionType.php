<?php

namespace App\Models;

use App\Enums\InfractionSeverity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'label', 'legal_basis', 'severity', 'potential_penalty', 'is_active'])]
class InfractionType extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function upheld(): HasMany
    {
        return $this->hasMany(UpheldInfraction::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function severityCase(): ?InfractionSeverity
    {
        return InfractionSeverity::tryFrom((string) $this->severity);
    }

    public function severityLabel(): ?string
    {
        return $this->severityCase() ? __($this->severityCase()->label()) : $this->severity;
    }
}
