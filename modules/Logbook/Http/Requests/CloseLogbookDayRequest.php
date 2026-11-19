<?php

namespace Modules\Logbook\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseLogbookDayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'instructions' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'instructions' => __('logbook::logbook.handovers.fields.instructions'),
        ];
    }
}
