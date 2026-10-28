<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'dpep_id', 'arrival', 'departure', 'is_inspected', 'is_infraction_recorded', 'inspection_id',
])]
class MrepPortCall extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'arrival' => 'datetime',
            'departure' => 'datetime',
            'is_inspected' => 'boolean',
            'is_infraction_recorded' => 'boolean',
        ];
    }

    public function dpep(): BelongsTo
    {
        return $this->belongsTo(MrepDpep::class, 'dpep_id');
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(ExternalInspection::class, 'inspection_id');
    }
}
