<?php

namespace Modules\Infractions\Http\Requests;

class ViolationReportRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'number' => ['nullable', 'string', 'max:60'],
            'notified_at' => ['nullable', 'date'],
            'is_signature_refused' => ['nullable', 'boolean'],
        ];
    }
}
