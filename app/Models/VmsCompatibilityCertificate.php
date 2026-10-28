<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'issued_on', 'expires_on'])]
class VmsCompatibilityCertificate extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['issued_on' => 'date', 'expires_on' => 'date'];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_on->isBefore(today());
    }
}
