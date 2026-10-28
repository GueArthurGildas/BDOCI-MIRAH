<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inspection_id', 'gear_id', 'mesh_mm', 'is_marking_compliant', 'is_compliant'])]
class InspectionGear extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'mesh_mm' => 'decimal:1',
            'is_marking_compliant' => 'boolean',
            'is_compliant' => 'boolean',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(ExternalInspection::class, 'inspection_id');
    }

    public function gear(): BelongsTo
    {
        return $this->belongsTo(Gear::class);
    }
}
