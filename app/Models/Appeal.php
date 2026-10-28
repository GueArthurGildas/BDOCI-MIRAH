<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['decision_id', 'filed_on', 'outcome'])]
class Appeal extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'filed_on' => 'date',
        ];
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(Decision::class);
    }
}
