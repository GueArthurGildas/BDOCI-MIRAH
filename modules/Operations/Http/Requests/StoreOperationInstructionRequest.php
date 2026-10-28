<?php

namespace Modules\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationInstructionRequest extends FormRequest
{
    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:5000']];
    }

    public function attributes(): array
    {
        return ['body' => __('operations::operations.fields.body')];
    }
}
