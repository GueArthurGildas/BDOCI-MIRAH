<?php

namespace App\Models;

use App\Enums\EntryDecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dpep_id', 'decision', 'decided_at', 'decision_maker'])]
class MrepEntryDecision extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'decision' => EntryDecision::class,
            'decided_at' => 'datetime',
        ];
    }

    public function dpep(): BelongsTo
    {
        return $this->belongsTo(MrepDpep::class, 'dpep_id');
    }
}
