<?php

namespace App\Models;

use App\Enums\CatchSource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'species_id', 'zone_id', 'period_start', 'period_end', 'quantity_kg', 'source'])]
class CatchDeclaration extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'source' => CatchSource::class,
            'period_start' => 'date',
            'period_end' => 'date',
            'quantity_kg' => 'float',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
