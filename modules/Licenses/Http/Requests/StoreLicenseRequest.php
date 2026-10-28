<?php

namespace Modules\Licenses\Http\Requests;

use App\Enums\LicenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLicenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['required', Rule::exists('vessels', 'id')->whereNull('deleted_at')],
            'number' => ['required', 'string', 'max:255', Rule::unique('licenses', 'number')],
            'type' => ['required', Rule::enum(LicenseType::class)],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
        ];
    }

    public function attributes(): array
    {
        return collect(['vessel_id' => 'vessel', 'number' => 'number', 'type' => 'type', 'valid_from' => 'valid_from', 'valid_until' => 'valid_until'])
            ->map(fn (string $field) => __("licenses::licenses.fields.{$field}"))
            ->all();
    }
}
