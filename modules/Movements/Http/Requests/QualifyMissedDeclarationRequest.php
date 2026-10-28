<?php

namespace Modules\Movements\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QualifyMissedDeclarationRequest extends FormRequest
{
    public function rules(): array
    {
        return ['qualification' => ['required', 'string', 'max:120']];
    }
}
