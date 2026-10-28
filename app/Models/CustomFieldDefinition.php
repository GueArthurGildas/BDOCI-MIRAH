<?php

namespace App\Models;

use App\Enums\CustomFieldType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['subject_type', 'code', 'label', 'type', 'is_visible'])]
class CustomFieldDefinition extends Model
{
    protected function casts(): array
    {
        return [
            'label' => 'array',
            'type' => CustomFieldType::class,
            'is_visible' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
