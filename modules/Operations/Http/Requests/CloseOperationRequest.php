<?php

namespace Modules\Operations\Http\Requests;

use App\Enums\LogbookDirection;
use App\Support\Logbook\LogbookChannels;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloseOperationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'result' => ['required', 'string', 'max:5000'],
            'infractions_noted' => ['nullable', 'string', 'max:5000'],
            'channel_id' => ['nullable', Rule::exists('reference_list_values', 'id')->where('reference_list_id', app(LogbookChannels::class)->ensure()->id)],
            'direction' => ['nullable', Rule::enum(LogbookDirection::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'result' => __('operations::operations.fields.result'),
            'infractions_noted' => __('operations::operations.fields.infractions_noted'),
            'channel_id' => __('operations::operations.fields.channel'),
            'direction' => __('operations::operations.fields.direction'),
        ];
    }
}
