<?php

namespace Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Communications\Http\Requests\Concerns\TranslatesAttributes;

class WhatsAppConsentRequest extends FormRequest
{
    use TranslatesAttributes;

    public function rules(): array
    {
        return [
            'number' => ['required', 'regex:/^\+?[\d\s().-]{8,20}$/'],
            'label' => ['nullable', 'string', 'max:160'],
        ];
    }
}
