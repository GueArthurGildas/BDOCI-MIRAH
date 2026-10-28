<?php

namespace Modules\Tracking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HandleAlertRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'verification_questions' => ['nullable', 'array'],
            'verification_questions.*' => ['boolean'],
            'is_vessel_contacted' => ['nullable', 'boolean'],
            'measures' => ['nullable', 'string', 'max:5000'],
            'operation_id' => ['nullable', 'exists:operations,id'],
            'close' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'measures' => __('tracking::tracking.alerts.measures'),
            'operation_id' => __('tracking::tracking.alerts.operation'),
        ];
    }
}
