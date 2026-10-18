<?php

namespace Modules\Logbook\Http\Requests;

use App\Enums\ObservationLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreObservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['nullable', 'exists:vessels,id'],
            'observed_at' => ['required', 'date', 'before_or_equal:now'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'level' => ['required', Rule::enum(ObservationLevel::class)],
            'description' => ['required', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __('logbook::logbook.fields.'.str_replace('_id', '', $field))])
            ->all();
    }
}
