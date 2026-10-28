<?php

namespace Modules\Licenses\Http\Requests;

use App\Enums\FnpDeregistrationReason;
use App\Enums\FnpRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FnpStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(FnpRegistration::class)],
            'deregistration_reason' => ['required_if:status,'.FnpRegistration::Deregistered->value, 'nullable', Rule::enum(FnpDeregistrationReason::class)],
            'status_on' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return collect(['status', 'deregistration_reason', 'status_on'])
            ->mapWithKeys(fn (string $field) => [$field => __("licenses::compliance.fnp.{$field}")])
            ->all();
    }
}
