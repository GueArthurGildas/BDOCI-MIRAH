<?php

namespace Modules\Administration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferenceListValueRequest extends FormRequest
{
    public function rules(): array
    {
        $isUpdate = $this->route('value') !== null;

        return [
            'code' => $isUpdate ? ['prohibited'] : [
                'required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('reference_list_values', 'code')->where('reference_list_id', $this->route('referenceList')->id),
            ],
            'label' => ['required', 'string', 'max:160'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => __('administration::reference_lists.code'),
            'label' => __('administration::reference_lists.label'),
            'sort_order' => __('administration::reference_lists.sort_order'),
        ];
    }
}
