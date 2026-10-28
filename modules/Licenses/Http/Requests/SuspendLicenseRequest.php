<?php

namespace Modules\Licenses\Http\Requests;

use App\Enums\SuspensionReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuspendLicenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::enum(SuspensionReason::class)],
            'started_on' => ['required', 'date'],
            'planned_end_on' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return collect(['reason', 'started_on', 'planned_end_on'])
            ->mapWithKeys(fn (string $field) => [$field => __("licenses::licenses.suspension.{$field}")])
            ->all();
    }
}
