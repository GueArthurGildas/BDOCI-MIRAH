<?php

namespace App\Models;

use App\Enums\SourceSystem;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'field', 'source', 'updated_at'])]
class VesselFieldProvenance extends Model
{
    protected $table = 'vessel_field_provenance';

    public $incrementing = false;

    protected $primaryKey = null;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'source' => SourceSystem::class,
            'updated_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
