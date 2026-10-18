<?php

namespace App\Models;

use App\Enums\InfractionStatus;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference', 'vessel_id', 'inspection_id', 'infraction_type_id', 'detected_at',
    'facts', 'evidence', 'status', 'decision', 'sanction',
])]
class Infraction extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'status' => InfractionStatus::class,
            'detected_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(InfractionType::class, 'infraction_type_id');
    }
}
