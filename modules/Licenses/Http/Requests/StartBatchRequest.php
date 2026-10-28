<?php

namespace Modules\Licenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartBatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_ids' => ['required', 'array', 'min:1'],
            'vessel_ids.*' => ['integer', 'exists:vessels,id'],
        ];
    }

    public function attributes(): array
    {
        return ['vessel_ids' => __('licenses::imports.vessels')];
    }
}
