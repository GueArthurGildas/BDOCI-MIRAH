<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['infraction_case_id', 'infraction_type_id', 'upheld_at'])]
class UpheldInfraction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'upheld_at' => 'datetime',
        ];
    }

    public function infractionCase(): BelongsTo
    {
        return $this->belongsTo(InfractionCase::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(InfractionType::class, 'infraction_type_id');
    }
}
