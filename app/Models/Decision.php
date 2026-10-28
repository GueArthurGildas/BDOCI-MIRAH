<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['follow_up_id', 'label', 'decided_by', 'decided_at', 'is_penalty'])]
class Decision extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
            'is_penalty' => 'boolean',
        ];
    }

    public function followUp(): BelongsTo
    {
        return $this->belongsTo(LitigationFollowUp::class, 'follow_up_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(Appeal::class);
    }
}
