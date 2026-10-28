<?php

namespace Modules\Administration\Http\Requests;

use App\Support\Authorization\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GrantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::enum(Permission::class)],
            'zone_id' => ['nullable', Rule::exists('zones', 'id')],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->filled('zone_id') && ! $this->filled('valid_from') && ! $this->filled('valid_to')) {
                    $validator->errors()->add('zone_id', __('administration::grants.needs_limit'));
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => __('administration::grants.action'),
            'zone_id' => __('administration::grants.zone'),
            'valid_from' => __('administration::grants.valid_from'),
            'valid_to' => __('administration::grants.valid_to'),
        ];
    }
}
