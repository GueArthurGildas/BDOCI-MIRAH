<?php

namespace Modules\Infractions\Http\Requests;

class PaymentRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999'],
            'currency' => ['nullable', 'string', 'size:3'],
            'paid_on' => ['nullable', 'date'],
        ];
    }
}
