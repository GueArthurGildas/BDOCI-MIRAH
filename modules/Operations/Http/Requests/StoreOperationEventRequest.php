<?php

namespace Modules\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationEventRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return ['occurred_at' => __('operations::operations.fields.occurred_at'), 'body' => __('operations::operations.fields.body')];
    }
}
