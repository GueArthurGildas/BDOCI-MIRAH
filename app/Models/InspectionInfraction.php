<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inspection_id', 'provision', 'stamp', 'measures'])]
class InspectionInfraction extends Model
{
    use HasFactory;

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(ExternalInspection::class, 'inspection_id');
    }
}
