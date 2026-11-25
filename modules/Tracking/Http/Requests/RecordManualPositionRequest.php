<?php

namespace Modules\Tracking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordManualPositionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['required', 'integer', 'exists:vessels,id'],
            'recorded_at' => ['required', 'date', 'before_or_equal:now'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id', 'required_without_all:latitude,longitude'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'speed_knots' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'activity' => ['nullable', 'in:moored,fishing,transit'],
            'control_slot_id' => ['nullable', 'integer', 'exists:control_slots,id'],
        ];
    }

    public function attributes(): array
    {
        return collect(['vessel_id', 'recorded_at', 'zone_id', 'latitude', 'longitude', 'speed_knots', 'activity'])
            ->mapWithKeys(fn (string $field) => [$field => __('tracking::tracking.manual.fields.'.$field)])
            ->all();
    }
}
