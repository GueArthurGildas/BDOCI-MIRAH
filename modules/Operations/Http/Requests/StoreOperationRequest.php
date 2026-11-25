<?php

namespace Modules\Operations\Http\Requests;

use App\Enums\LogbookDirection;
use App\Support\Logbook\LogbookChannels;
use App\Support\Operations\WeatherScales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOperationRequest extends FormRequest
{
    public function rules(): array
    {
        $scales = app(WeatherScales::class);

        return [
            'category_id' => ['required', 'exists:operation_categories,id'],
            'name' => ['required', 'string', 'max:120', 'regex:/[A-Za-z0-9]/'],
            'started_at' => ['nullable', 'date', 'before_or_equal:now'],
            'origin' => ['nullable', 'string', 'max:60'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'vessel_ids' => ['nullable', 'array'],
            'vessel_ids.*' => ['exists:vessels,id'],
            'first_event' => ['nullable', 'string', 'max:5000'],
            'temperature' => ['nullable', 'numeric', 'between:-10,60'],
            'pressure' => ['nullable', 'numeric', 'between:800,1200'],
            'visibility' => ['nullable', Rule::in(array_keys($scales->options(WeatherScales::VISIBILITY)))],
            'cloud_cover' => ['nullable', Rule::in(array_keys($scales->options(WeatherScales::CLOUD_COVER)))],
            'wind_beaufort' => ['nullable', 'integer', 'between:0,12'],
            'sea_state' => ['nullable', Rule::in(array_keys($scales->options(WeatherScales::SEA_STATE)))],
            'weather_notes' => ['nullable', 'string', 'max:5000'],
            'vessel_position' => ['nullable', 'string', 'max:2000'],
            'vessel_activity' => ['nullable', 'string', 'max:2000'],
            'comments' => ['nullable', 'string', 'max:5000'],
            'channel_id' => ['required', Rule::exists('reference_list_values', 'id')->where('reference_list_id', app(LogbookChannels::class)->ensure()->id)],
            'direction' => ['required', Rule::enum(LogbookDirection::class)],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->reject(fn (string $field) => str_ends_with($field, '.*'))
            ->mapWithKeys(fn (string $field) => [$field => __('operations::operations.fields.'.str_replace('_ids', 's', str_replace('_id', '', $field)))])
            ->all();
    }
}
