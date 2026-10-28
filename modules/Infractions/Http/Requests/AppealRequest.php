<?php

namespace Modules\Infractions\Http\Requests;

class AppealRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'filed_on' => ['required', 'date'],
            'outcome' => ['nullable', 'string', 'max:255'],
        ];
    }
}
