<?php

namespace Modules\Movements\Http\Requests;

use App\Enums\EezDeclarationType;
use App\Enums\EezReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreEezDeclarationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['nullable', 'exists:vessels,id'],
            'type' => ['required', Rule::enum(EezDeclarationType::class)],
            'received_at' => ['required', 'date', 'before_or_equal:now'],
            'reception_channel_id' => ['nullable', Rule::exists('reference_list_values', 'id')],
            'crossed_at' => ['nullable', 'date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'route' => ['nullable', 'string', 'max:120'],
            'speed' => ['nullable', 'numeric', 'between:0,99.99'],
            'destination' => ['nullable', 'string', 'max:160'],
            'reason' => ['nullable', Rule::enum(EezReason::class)],
            'declared_name' => ['nullable', 'required_without:vessel_id', 'string', 'max:190'],
            'declared_imo' => ['nullable', 'string', 'max:20'],
            'declared_mmsi' => ['nullable', 'string', 'max:20'],
            'declared_ircs' => ['nullable', 'string', 'max:20'],
            'declared_flag' => ['nullable', 'string', 'max:80'],
            'declared_owner' => ['nullable', 'string', 'max:190'],
            'catches' => ['nullable', 'array', 'max:10'],
            'catches.*.species_id' => ['required_with:catches.*.weight_kg', 'nullable', 'exists:species,id'],
            'catches.*.weight_kg' => ['required_with:catches.*.species_id', 'nullable', 'numeric', 'gt:0', 'max:9999999999'],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys(Arr::except($this->rules(), ['catches', 'catches.*.species_id', 'catches.*.weight_kg'])))
            ->mapWithKeys(fn (string $field) => [$field => __('movements::eez.fields.'.str_replace('_id', '', $field))])
            ->all();
    }

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if ($key === null) {
            $data['catches'] = collect($data['catches'] ?? [])
                ->filter(fn (array $catch) => filled($catch['species_id'] ?? null) && filled($catch['weight_kg'] ?? null))
                ->values()
                ->all();
        }

        return $data;
    }
}
