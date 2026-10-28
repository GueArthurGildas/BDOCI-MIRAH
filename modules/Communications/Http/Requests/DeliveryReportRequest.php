<?php

namespace Modules\Communications\Http\Requests;

use App\Enums\MessageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryReportRequest extends FormRequest
{
    private const REPORTABLE = [MessageStatus::Acknowledged, MessageStatus::Bounced, MessageStatus::Failed];

    public function authorize(): bool
    {
        $secret = config('communications.webhook_secret');

        return filled($secret) && hash_equals((string) $secret, (string) $this->header('X-Webhook-Secret'));
    }

    public function rules(): array
    {
        return [
            'message_id' => ['required', 'integer', Rule::exists('sent_messages', 'id')],
            'event' => ['required', Rule::in(array_map(fn (MessageStatus $status) => $status->value, self::REPORTABLE))],
            'reply_received' => ['nullable', 'boolean'],
        ];
    }
}
