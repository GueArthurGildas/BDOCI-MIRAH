<?php

namespace Modules\Communications\Http\Requests;

use App\Enums\DispatchMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Communications\Http\Requests\Concerns\TranslatesAttributes;
use Modules\Communications\Support\StopConditions;
use Modules\Communications\Support\TriggerEvents;

class SaveRuleRequest extends FormRequest
{
    use TranslatesAttributes;

    public function rules(): array
    {
        return [
            'message_template_id' => ['required', Rule::exists('message_templates', 'id')],
            'trigger_event' => ['required', Rule::in(TriggerEvents::codes())],
            'recipient' => ['required', 'string', 'max:190'],
            'frequency_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'daily_cap' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'stop_condition' => ['nullable', Rule::in(StopConditions::codes())],
            'mode' => ['required', Rule::enum(DispatchMode::class)],
            'is_active' => ['required', 'boolean'],
            'editable_by_permission_group_id' => ['nullable', Rule::exists('permission_groups', 'id')],
        ];
    }
}
