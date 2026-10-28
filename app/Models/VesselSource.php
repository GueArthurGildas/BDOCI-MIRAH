<?php

namespace App\Models;

use App\Enums\MatchingCriterion;
use App\Enums\MatchingStatus;
use App\Enums\SourceSystem;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'source', 'external_id', 'last_import_at', 'raw_data', 'matching_status', 'candidate_vessel_id', 'matching_criterion', 'settled_by', 'settled_at'])]
class VesselSource extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'source' => SourceSystem::class,
            'matching_status' => MatchingStatus::class,
            'matching_criterion' => MatchingCriterion::class,
            'raw_data' => 'array',
            'last_import_at' => 'datetime',
            'settled_at' => 'datetime',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Vessel::class, 'candidate_vessel_id');
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}
