<?php

namespace Modules\Tracking\Http\Requests;

use App\Enums\AlertRuleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAlertRuleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => [$this->route('rule') ? 'prohibited' : 'required', 'string', 'max:40', 'unique:alert_rules,code'],
            'type' => ['required', Rule::enum(AlertRuleType::class)],
            'threshold_hours' => ['nullable', 'numeric', 'min:0.5', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => __('tracking::tracking.rules.code'),
            'type' => __('tracking::tracking.rules.type'),
            'threshold_hours' => __('tracking::tracking.rules.threshold_hours'),
        ];
    }
}
