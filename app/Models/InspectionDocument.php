<?php

namespace App\Models;

use App\Enums\InspectionDocumentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inspection_id', 'type', 'is_compliant'])]
class InspectionDocument extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => InspectionDocumentType::class,
            'is_compliant' => 'boolean',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(ExternalInspection::class, 'inspection_id');
    }
}
