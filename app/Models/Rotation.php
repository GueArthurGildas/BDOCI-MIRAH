<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'day', 'start_time', 'end_time'])]
class Rotation extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'day' => 'date',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(ShiftHandover::class, 'rotation_id');
    }
}
