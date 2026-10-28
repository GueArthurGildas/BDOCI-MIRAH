<?php

namespace App\Models;

use App\Enums\VmsRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vessel_id', 'requested_on', 'status'])]
class VmsAssessmentRequest extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'requested_on' => 'date',
            'status' => VmsRequestStatus::class,
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(CompatibilityAssessment::class, 'assessment_request_id');
    }
}
