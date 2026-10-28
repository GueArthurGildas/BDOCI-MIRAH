<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vms_failure_id', 'document_id', 'attested_on'])]
class WorkingOrderAttestation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'attested_on' => 'date',
        ];
    }

    public function failure(): BelongsTo
    {
        return $this->belongsTo(VmsFailure::class, 'vms_failure_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
