<?php

namespace Modules\Operations\Http\Requests;

use App\Enums\LogbookDirection;
use App\Support\Logbook\LogbookChannels;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOperationEventRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
            'channel_id' => ['required', Rule::exists('reference_list_values', 'id')->where('reference_list_id', app(LogbookChannels::class)->ensure()->id)],
            'direction' => ['required', Rule::enum(LogbookDirection::class)],
            'body' => ['required', 'string', 'max:5000'],
            'is_order_or_decision' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'occurred_at' => __('operations::operations.fields.occurred_at'),
            'channel_id' => __('operations::operations.fields.channel'),
            'direction' => __('operations::operations.fields.direction'),
            'body' => __('operations::operations.fields.body'),
        ];
    }
}
