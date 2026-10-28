<?php

namespace App\Models;

use App\Enums\ExternalListType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['institution_id', 'type', 'label'])]
class ExternalList extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => ExternalListType::class,
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
