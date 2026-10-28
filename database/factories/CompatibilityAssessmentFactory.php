<?php

namespace Database\Factories;

use App\Enums\AssessmentResult;
use App\Models\VmsAssessmentRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompatibilityAssessmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assessment_request_id' => VmsAssessmentRequest::factory(),
            'result' => AssessmentResult::Compatible,
            'assessed_on' => today(),
        ];
    }
}
