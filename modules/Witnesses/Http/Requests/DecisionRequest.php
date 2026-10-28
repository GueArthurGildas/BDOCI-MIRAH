<?php

namespace Modules\Witnesses\Http\Requests;

use App\Enums\ReportDecisionType;
use Illuminate\Validation\Rule;

class DecisionRequest extends WitnessesRequest
{
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(ReportDecisionType::class)],
        ];
    }
}
