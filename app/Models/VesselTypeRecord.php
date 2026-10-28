<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'label'])]
class VesselTypeRecord extends Model
{
    use HasFactory;

    protected $table = 'vessel_types';

    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class, 'vessel_type_id');
    }
}
