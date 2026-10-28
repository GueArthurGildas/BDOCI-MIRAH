<?php

namespace Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Communications\Http\Requests\Concerns\TranslatesAttributes;

class SaveEmailConnectorRequest extends FormRequest
{
    use TranslatesAttributes;

    public function rules(): array
    {
        return [
            'supplier' => ['required', 'string', 'max:80'],
            'domain' => ['nullable', 'string', 'max:190'],
            'mailer' => ['nullable', 'string', 'max:40', 'in:'.implode(',', array_keys(config('mail.mailers')))],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
