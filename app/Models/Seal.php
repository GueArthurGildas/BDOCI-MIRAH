<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['infraction_case_id', 'number', 'placed_at'])]
class Seal extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
        ];
    }

    public function infractionCase(): BelongsTo
    {
        return $this->belongsTo(InfractionCase::class);
    }
}
