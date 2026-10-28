<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ers_declaration_id', 'species_id', 'presentation_id', 'conversion_factor', 'live_weight_kg', 'fao_zone', 'gear_id'])]
class ErsCatchLine extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'float',
            'live_weight_kg' => 'float',
        ];
    }

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(ErsDeclaration::class, 'ers_declaration_id');
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    public function gear(): BelongsTo
    {
        return $this->belongsTo(Gear::class);
    }
}
