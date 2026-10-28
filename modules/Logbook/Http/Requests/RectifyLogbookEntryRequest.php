<?php

namespace Modules\Logbook\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RectifyLogbookEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return ['content' => ['required', 'string', 'max:5000']];
    }

    public function attributes(): array
    {
        return ['content' => __('logbook::logbook.entries.fields.content')];
    }
}
