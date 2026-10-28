<?php

namespace Modules\Infractions\Http\Requests;

class EvidenceRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
