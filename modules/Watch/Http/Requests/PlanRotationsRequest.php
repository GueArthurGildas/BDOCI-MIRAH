<?php

namespace Modules\Watch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanRotationsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_on' => ['required', 'date'],
            'days_count' => ['required', 'integer', 'between:1,62'],
        ];
    }

    public function attributes(): array
    {
        return ['start_on' => __('watch::watch.fields.start_on'), 'days_count' => __('watch::watch.fields.days_count')];
    }
}
