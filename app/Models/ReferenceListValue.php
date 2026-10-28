<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reference_list_id', 'code', 'label', 'sort_order', 'is_active'])]
class ReferenceListValue extends Model
{
    use Audited;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(ReferenceList::class, 'reference_list_id');
    }
}
