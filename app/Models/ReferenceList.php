<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'label'])]
class ReferenceList extends Model
{
    use Audited;

    public function values(): HasMany
    {
        return $this->hasMany(ReferenceListValue::class)->orderBy('sort_order')->orderBy('label');
    }
}
