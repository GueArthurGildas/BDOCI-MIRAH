<?php

namespace App\Models;

use App\Enums\OperationCategoryCode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'label'])]
class OperationCategory extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'code' => OperationCategoryCode::class,
        ];
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class, 'category_id');
    }
}
