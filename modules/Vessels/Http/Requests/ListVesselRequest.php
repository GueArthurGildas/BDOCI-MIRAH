<?php

namespace Modules\Vessels\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListVesselRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'external_list_id' => ['required', 'exists:external_lists,id'],
            'listed_from' => ['nullable', 'date'],
            'listed_until' => ['nullable', 'date', 'after_or_equal:listed_from'],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __("vessels::vessels.lists.fields.{$field}")])
            ->all();
    }
}
