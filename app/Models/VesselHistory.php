<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'former_name', 'former_flag_country_id', 'changed_on'])]
class VesselHistory extends Model
{
    use HasFactory;

    protected $table = 'vessel_history';

    protected function casts(): array
    {
        return [
            'changed_on' => 'date',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function formerFlagCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'former_flag_country_id');
    }
}
