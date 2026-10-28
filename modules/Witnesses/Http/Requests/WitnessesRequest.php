<?php

namespace Modules\Witnesses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class WitnessesRequest extends FormRequest
{
    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __('witnesses::witnesses.fields.'.str($field)->replace('.*', '')->afterLast('.'))])
            ->all();
    }
}
