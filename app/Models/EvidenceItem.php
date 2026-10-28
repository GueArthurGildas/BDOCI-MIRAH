<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['infraction_case_id', 'description', 'document_id'])]
class EvidenceItem extends Model
{
    use HasFactory;

    public function infractionCase(): BelongsTo
    {
        return $this->belongsTo(InfractionCase::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
