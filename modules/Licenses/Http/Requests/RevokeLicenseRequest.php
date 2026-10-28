<?php

namespace Modules\Licenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevokeLicenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
            'revoked_on' => ['required', 'date'],
            'is_repeat_offense' => ['nullable', 'boolean'],
            'infraction_case_id' => ['nullable', 'exists:infraction_cases,id'],
        ];
    }

    public function attributes(): array
    {
        return collect(['reason', 'revoked_on', 'is_repeat_offense'])
            ->mapWithKeys(fn (string $field) => [$field => __("licenses::licenses.revocation.{$field}")])
            ->all();
    }
}
