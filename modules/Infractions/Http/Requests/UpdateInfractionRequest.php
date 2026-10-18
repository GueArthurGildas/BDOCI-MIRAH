<?php

namespace Modules\Infractions\Http\Requests;

use App\Enums\InfractionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInfractionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(InfractionStatus::class)],
            'decision' => ['nullable', 'string', 'max:5000'],
            'sanction' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __("infractions::infractions.fields.{$field}")])
            ->all();
    }
}
