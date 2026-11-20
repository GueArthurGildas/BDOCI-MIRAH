<?php

namespace Modules\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachOperationDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,txt,eml,msg', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return ['file' => __('operations::operations.fields.file')];
    }
}
