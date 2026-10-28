<?php

namespace Modules\Logbook\Http\Requests;

use App\Enums\LogbookDirection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLogbookEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
            'channel_id' => ['required', 'exists:reference_list_values,id'],
            'direction' => ['required', Rule::enum(LogbookDirection::class)],
            'content' => ['required', 'string', 'max:5000'],
            'is_order_or_decision' => ['nullable', 'boolean'],
            'operation_id' => ['nullable', 'exists:operations,id'],
            'vessel_ids' => ['nullable', 'array'],
            'vessel_ids.*' => ['exists:vessels,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'occurred_at' => __('logbook::logbook.entries.fields.occurred_at'),
            'channel_id' => __('logbook::logbook.entries.fields.channel'),
            'direction' => __('logbook::logbook.entries.fields.direction'),
            'content' => __('logbook::logbook.entries.fields.content'),
            'operation_id' => __('logbook::logbook.entries.fields.operation'),
            'vessel_ids' => __('logbook::logbook.entries.fields.vessels'),
        ];
    }
}
