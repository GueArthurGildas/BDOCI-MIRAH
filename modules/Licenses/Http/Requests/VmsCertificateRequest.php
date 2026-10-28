<?php

namespace Modules\Licenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VmsCertificateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'issued_on' => ['required', 'date'],
            'expires_on' => ['required', 'date', 'after:issued_on'],
        ];
    }

    public function attributes(): array
    {
        return collect(['issued_on', 'expires_on'])
            ->mapWithKeys(fn (string $field) => [$field => __("licenses::compliance.vms.{$field}")])
            ->all();
    }
}
