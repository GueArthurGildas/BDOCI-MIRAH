<?php

namespace Modules\Administration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferenceListRequest extends FormRequest
{
    public function rules(): array
    {
        $isUpdate = $this->route('referenceList') !== null;

        return [
            'code' => $isUpdate ? ['prohibited'] : ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/', Rule::unique('reference_lists', 'code')],
            'label' => ['required', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => __('administration::reference_lists.code'),
            'label' => __('administration::reference_lists.label'),
        ];
    }
}
