<?php

namespace Modules\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseOperationRequest extends FormRequest
{
    public function rules(): array
    {
        return ['result' => ['required', 'string', 'max:5000']];
    }

    public function attributes(): array
    {
        return ['result' => __('operations::operations.fields.result')];
    }
}
