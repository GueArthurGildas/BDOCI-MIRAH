<?php

namespace App\Models;

use App\Enums\WitnessCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'category', 'email', 'phone'])]
class Witness extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'category' => WitnessCategory::class,
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(WitnessReport::class, 'witness_id');
    }
}
