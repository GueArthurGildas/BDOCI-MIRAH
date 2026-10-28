<?php

namespace Modules\Licenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterYearRequest extends FormRequest
{
    public function rules(): array
    {
        return ['year' => ['required', 'integer', 'between:2000,'.(now()->year + 1)]];
    }

    public function attributes(): array
    {
        return ['year' => __('licenses::register.year')];
    }
}
