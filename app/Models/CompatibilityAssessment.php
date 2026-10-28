<?php

namespace App\Models;

use App\Enums\AssessmentResult;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assessment_request_id', 'result', 'assessed_on'])]
class CompatibilityAssessment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'result' => AssessmentResult::class,
            'assessed_on' => 'date',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(VmsAssessmentRequest::class, 'assessment_request_id');
    }
}
