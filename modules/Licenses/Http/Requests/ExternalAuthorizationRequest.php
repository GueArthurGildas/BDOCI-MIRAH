<?php

namespace Modules\Licenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExternalAuthorizationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'institution_id' => ['required', 'exists:institutions,id'],
            'number' => ['nullable', 'string', 'max:80'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['required', 'date', 'after_or_equal:valid_from'],
        ];
    }

    public function attributes(): array
    {
        return collect(['institution_id', 'number', 'valid_from', 'valid_to'])
            ->mapWithKeys(fn (string $field) => [$field => __("licenses::compliance.external.{$field}")])
            ->all();
    }
}
