<?php

namespace Modules\Infractions\Http\Requests;

class SealRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:60'],
            'placed_at' => ['nullable', 'date'],
        ];
    }
}
