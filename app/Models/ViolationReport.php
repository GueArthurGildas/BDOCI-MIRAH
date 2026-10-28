<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['infraction_case_id', 'number', 'notified_at', 'is_signature_refused', 'document_id'])]
class ViolationReport extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
            'is_signature_refused' => 'boolean',
        ];
    }

    public function infractionCase(): BelongsTo
    {
        return $this->belongsTo(InfractionCase::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
