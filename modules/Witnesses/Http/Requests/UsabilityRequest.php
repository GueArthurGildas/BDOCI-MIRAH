<?php

namespace Modules\Witnesses\Http\Requests;

class UsabilityRequest extends WitnessesRequest
{
    public function rules(): array
    {
        return [
            'is_usable' => ['required', 'boolean'],
            'interview_report' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
