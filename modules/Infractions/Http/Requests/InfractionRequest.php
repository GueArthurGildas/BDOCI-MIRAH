<?php

namespace Modules\Infractions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class InfractionRequest extends FormRequest
{
    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __('infractions::infractions.fields.'.str($field)->replace('.*', '')->afterLast('.'))])
            ->all();
    }
}
