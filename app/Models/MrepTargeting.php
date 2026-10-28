<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['year', 'objective_pct', 'achieved_pct', 'target'])]
class MrepTargeting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'objective_pct' => 'decimal:2',
            'achieved_pct' => 'decimal:2',
        ];
    }
}
