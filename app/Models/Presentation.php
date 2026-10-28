<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'label', 'conversion_factor'])]
class Presentation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'float',
        ];
    }
}
