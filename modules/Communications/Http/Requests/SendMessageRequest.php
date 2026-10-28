<?php

namespace Modules\Communications\Http\Requests;

use App\Enums\CommunicationChannelCode;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Communications\Http\Requests\Concerns\TranslatesAttributes;

class SendMessageRequest extends FormRequest
{
    use TranslatesAttributes;

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::enum(CommunicationChannelCode::class)],
            'message_template_id' => ['required', Rule::exists('message_templates', 'id')->where('is_active', true)],
            'vessel_id' => ['nullable', Rule::exists('vessels', 'id')->whereNull('deleted_at')],
            'recipient' => ['required', 'string', 'max:190'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $channel = CommunicationChannelCode::from($this->input('channel'));
            $template = MessageTemplate::find($this->input('message_template_id'));
            $recipient = (string) $this->input('recipient');

            if ($channel !== CommunicationChannelCode::InApp && $template->channel !== $channel) {
                $validator->errors()->add('message_template_id', __('communications::messages.template_channel_mismatch'));
            }

            $valid = match ($channel) {
                CommunicationChannelCode::InApp => User::where('email', $recipient)->where('is_active', true)->exists(),
                CommunicationChannelCode::Email => filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false,
                CommunicationChannelCode::WhatsApp => preg_match('/^\+?[\d\s().-]{8,20}$/', $recipient) === 1,
            };

            if (! $valid) {
                $validator->errors()->add('recipient', __("communications::messages.invalid_recipient.{$channel->value}"));
            }
        }];
    }
}
