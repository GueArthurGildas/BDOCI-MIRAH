<?php

namespace App\Models;

use App\Enums\ZoneRuleSegment;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['zone_id', 'segment', 'nautical_miles'])]
class ZoneRule extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'segment' => ZoneRuleSegment::class,
            'nautical_miles' => 'integer',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
