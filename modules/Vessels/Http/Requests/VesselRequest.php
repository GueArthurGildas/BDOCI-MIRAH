<?php

namespace Modules\Vessels\Http\Requests;

use App\Enums\VesselCategory;
use App\Enums\VesselStatus;
use App\Enums\VesselType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VesselRequest extends FormRequest
{
    public function rules(): array
    {
        $vessel = $this->route('vessel');

        return [
            'name' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:50', Rule::unique('vessels')->ignore($vessel)],
            'imo_number' => ['nullable', 'string', 'max:20', Rule::unique('vessels')->ignore($vessel)],
            'call_sign' => ['nullable', 'string', 'max:20'],
            'flag' => ['required', Rule::in(config('surveillance.flag_states'))],
            'type' => ['required', Rule::enum(VesselType::class)],
            'category' => ['required', Rule::enum(VesselCategory::class)],
            'owner' => ['nullable', 'string', 'max:255'],
            'length_meters' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'gross_tonnage' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(VesselStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __("vessels::vessels.fields.{$field}")])
            ->all();
    }
}
