<?php

namespace Modules\Communications\Http\Requests;

use App\Enums\CommunicationChannelCode;
use App\Support\Documents\GeneratedDocuments;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotifyContactsRequest extends FormRequest
{
    public const MAX_CONTACTS = 100;

    public const CHANNELS = [CommunicationChannelCode::Email, CommunicationChannelCode::WhatsApp];

    public function rules(): array
    {
        return [
            'contacts' => ['required', 'array', 'min:1', 'max:'.self::MAX_CONTACTS],
            'contacts.*' => ['integer', 'distinct', 'exists:contacts,id'],
            'channel' => ['required', Rule::in(array_map(fn (CommunicationChannelCode $channel) => $channel->value, self::CHANNELS))],
            'message_template_id' => ['nullable', Rule::exists('message_templates', 'id')->where('is_active', true)],
            'vessel_id' => ['nullable', Rule::exists('vessels', 'id')->whereNull('deleted_at')],
            'subject' => ['nullable', 'string', 'max:190', Rule::requiredIf(fn () => $this->input('channel') === CommunicationChannelCode::Email->value)],
            'body' => ['required', 'string', 'max:5000'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['string', Rule::in(GeneratedDocuments::KINDS)],
            'logbook_day_id' => ['nullable', 'exists:logbook_days,id', Rule::requiredIf(fn () => in_array(GeneratedDocuments::LOGBOOK_DAY, (array) $this->input('documents'), true))],
            'observation_id' => ['nullable', 'exists:observations,id', Rule::requiredIf(fn () => in_array(GeneratedDocuments::OBSERVATION, (array) $this->input('documents'), true))],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if (in_array(GeneratedDocuments::VESSEL_SHEET, (array) $this->input('documents'), true) && ! $this->filled('vessel_id')) {
                $validator->errors()->add('vessel_id', __('communications::directory.errors.vessel_required'));
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'contacts' => __('communications::directory.recipients'),
            'channel' => __('communications::fields.channel'),
            'subject' => __('communications::fields.subject'),
            'body' => __('communications::fields.body'),
            'vessel_id' => __('communications::fields.vessel_id'),
            'logbook_day_id' => __('communications::directory.documents.logbook_day_subject'),
            'observation_id' => __('communications::directory.documents.observation_subject'),
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return [
            ...collect($this->validated())->except(['contacts'])->all(),
            'contact_ids' => array_map('intval', $this->validated('contacts')),
        ];
    }
}
