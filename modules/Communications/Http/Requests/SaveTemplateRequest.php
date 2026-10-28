<?php

namespace Modules\Communications\Http\Requests;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageForm;
use App\Enums\MessageLanguage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Communications\Http\Requests\Concerns\TranslatesAttributes;

class SaveTemplateRequest extends FormRequest
{
    use TranslatesAttributes;

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:60', Rule::unique('message_templates', 'code')->ignore($this->route('template'))],
            'channel' => ['required', Rule::in([CommunicationChannelCode::Email->value, CommunicationChannelCode::WhatsApp->value])],
            'language' => ['required', Rule::enum(MessageLanguage::class)],
            'subject' => ['nullable', 'string', 'max:255', 'required_if:channel,email'],
            'body' => ['required', 'string', 'max:5000'],
            'form' => ['required', Rule::enum(MessageForm::class)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
