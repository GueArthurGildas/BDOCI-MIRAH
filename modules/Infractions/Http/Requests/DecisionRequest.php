<?php

namespace Modules\Infractions\Http\Requests;

class DecisionRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'is_penalty' => ['nullable', 'boolean'],
        ];
    }
}
