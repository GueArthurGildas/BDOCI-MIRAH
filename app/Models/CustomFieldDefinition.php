<?php

namespace App\Models;

use App\Enums\CustomFieldType;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['object_type', 'code', 'label', 'type', 'is_visible'])]
class CustomFieldDefinition extends Model
{
    use Audited;

    protected function casts(): array
    {
        return ['type' => CustomFieldType::class, 'is_visible' => 'boolean'];
    }
}
