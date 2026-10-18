<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['fao_code', 'name', 'scientific_name'])]
class Species extends Model
{
    use HasFactory;

    public function catchDeclarations(): HasMany
    {
        return $this->hasMany(CatchDeclaration::class);
    }
}
