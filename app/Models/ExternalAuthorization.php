<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'institution_id', 'number', 'valid_from', 'valid_to'])]
class ExternalAuthorization extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['valid_from' => 'date', 'valid_to' => 'date'];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function isExpired(): bool
    {
        return $this->valid_to->isBefore(today());
    }
}
