<?php

namespace Modules\Licenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'vessel_ids' => ['nullable', 'array'],
            'vessel_ids.*' => ['integer', 'exists:vessels,id'],
        ];
    }

    public function attributes(): array
    {
        return ['file' => __('licenses::licenses.documents.file'), 'vessel_ids' => __('licenses::licenses.documents.also_vessels')];
    }
}
