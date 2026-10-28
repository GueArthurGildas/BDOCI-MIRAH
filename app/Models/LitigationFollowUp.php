<?php

namespace App\Models;

use App\Enums\FollowUpType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['infraction_case_id', 'type', 'opened_at', 'closed_at'])]
class LitigationFollowUp extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => FollowUpType::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function infractionCase(): BelongsTo
    {
        return $this->belongsTo(InfractionCase::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class, 'follow_up_id');
    }
}
