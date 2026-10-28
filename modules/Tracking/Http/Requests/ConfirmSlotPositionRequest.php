<?php

namespace Modules\Tracking\Http\Requests;

use App\Enums\SlotResult;
use App\Enums\SlotTestResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmSlotPositionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'emission_test' => ['required', Rule::enum(SlotTestResult::class)],
            'speed_activity_test' => ['required', Rule::enum(SlotTestResult::class)],
            'trajectory_test' => ['required', Rule::enum(SlotTestResult::class)],
            'result' => ['nullable', Rule::enum(SlotResult::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'emission_test' => __('tracking::tracking.slots.emission_test'),
            'speed_activity_test' => __('tracking::tracking.slots.speed_activity_test'),
            'trajectory_test' => __('tracking::tracking.slots.trajectory_test'),
            'result' => __('tracking::tracking.slots.result'),
        ];
    }
}
