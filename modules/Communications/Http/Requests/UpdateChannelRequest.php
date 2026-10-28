<?php

namespace Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Communications\Http\Requests\Concerns\TranslatesAttributes;

class UpdateChannelRequest extends FormRequest
{
    use TranslatesAttributes;

    public function rules(): array
    {
        return [
            'is_auto_dispatch_enabled' => ['required', 'boolean'],
            'is_consent_required' => ['required', 'boolean'],
        ];
    }
}
