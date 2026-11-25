<?php

namespace Modules\Logbook\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftHandoverRequest extends FormRequest
{
    public function rules(): array
    {
        $activeHuman = Rule::exists('users', 'id')->where('is_active', true)->where('type', UserType::Human->value);

        return [
            'handed_over_at' => ['nullable', 'date', 'before_or_equal:now'],
            'taken_over_by' => ['required', 'integer', $activeHuman],
            'instructions' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'handed_over_at' => __('logbook::logbook.handovers.fields.handed_over_at'),
            'taken_over_by' => __('logbook::logbook.handovers.fields.incoming'),
            'instructions' => __('logbook::logbook.handovers.fields.instructions'),
        ];
    }
}
