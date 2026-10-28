<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code'])]
class ReferenceList extends Model
{
    public function values(): HasMany
    {
        return $this->hasMany(ReferenceValue::class)->orderBy('sort_order');
    }
}
