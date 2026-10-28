<?php

namespace Modules\Watch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStandingInstructionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['nullable', 'exists:vessels,id'],
            'body' => ['required', 'string', 'max:5000'],
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
        ];
    }

    public function attributes(): array
    {
        return [
            'vessel_id' => __('watch::watch.fields.vessel'),
            'body' => __('watch::watch.fields.body'),
            'started_on' => __('watch::watch.fields.started_on'),
            'ended_on' => __('watch::watch.fields.ended_on'),
        ];
    }
}
