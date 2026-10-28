<?php

namespace App\Models;

use App\Enums\SeizureDecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['infraction_case_id', 'item', 'decision'])]
class Seizure extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'decision' => SeizureDecision::class,
        ];
    }

    public function infractionCase(): BelongsTo
    {
        return $this->belongsTo(InfractionCase::class);
    }
}
