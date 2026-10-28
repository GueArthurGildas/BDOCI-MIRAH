<?php

namespace Modules\Missions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class MissionRequest extends FormRequest
{
    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __('missions::missions.fields.'.str($field)->replace('.*', '')->afterLast('.'))])
            ->all();
    }
}
