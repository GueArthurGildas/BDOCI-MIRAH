<?php

namespace App\Models;

use App\Enums\IdentifierType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'type', 'value', 'valid_from', 'valid_to'])]
class VesselIdentifier extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => IdentifierType::class,
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
