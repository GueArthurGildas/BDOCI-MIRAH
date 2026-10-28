<?php

namespace Modules\Licenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportFileRequest extends FormRequest
{
    public function rules(): array
    {
        return ['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']];
    }

    public function attributes(): array
    {
        return ['file' => __('licenses::imports.file')];
    }
}
