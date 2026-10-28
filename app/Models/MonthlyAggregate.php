<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['year_month', 'vessel_id', 'emission_rate', 'silences_count'])]
class MonthlyAggregate extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'emission_rate' => 'float',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
