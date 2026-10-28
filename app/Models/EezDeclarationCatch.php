<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['eez_declaration_id', 'species_id', 'weight_kg'])]
class EezDeclarationCatch extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['weight_kg' => 'float'];
    }

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(EezDeclaration::class, 'eez_declaration_id');
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
