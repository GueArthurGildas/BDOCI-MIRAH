<?php

namespace Modules\Administration\Http\Requests;

use App\Enums\CustomFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomFieldRequest extends FormRequest
{
    public function rules(): array
    {
        $isUpdate = $this->route('customField') !== null;

        return [
            'object_type' => $isUpdate ? ['prohibited'] : ['required', Rule::in(array_keys(config('custom-fields.objects')))],
            'code' => $isUpdate ? ['prohibited'] : [
                'required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('custom_field_definitions', 'code')->where('object_type', $this->input('object_type')),
            ],
            'type' => $isUpdate ? ['prohibited'] : ['required', Rule::enum(CustomFieldType::class)],
            'label' => ['required', 'string', 'max:120'],
            'is_visible' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'object_type' => __('administration::custom_fields.object'),
            'code' => __('administration::custom_fields.code'),
            'type' => __('administration::custom_fields.type'),
            'label' => __('administration::custom_fields.label'),
        ];
    }
}
