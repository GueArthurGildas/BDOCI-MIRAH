<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['legal_entity_id', 'approved_until'])]
class ApprovedInstaller extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'approved_until' => 'date',
        ];
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }
}
