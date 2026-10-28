<?php

namespace Modules\Vessels\Http\Requests;

use App\Enums\FleetSegment;
use App\Enums\FleetType;
use App\Enums\LegalStatus;
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
            'call_sign' => ['nullable', 'string', 'max:20', Rule::unique('vessels')->ignore($vessel)],
            'flag' => ['required', Rule::in(config('surveillance.flag_states'))],
            'type' => ['required', Rule::enum(VesselType::class)],
            'category' => ['required', Rule::enum(VesselCategory::class)],
            'owner' => ['nullable', 'string', 'max:255'],
            'length_meters' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'gross_tonnage' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(VesselStatus::class)],
            'fleet_segment' => ['nullable', Rule::enum(FleetSegment::class)],
            'fleet_type' => ['nullable', Rule::enum(FleetType::class)],
            'legal_status' => ['nullable', Rule::enum(LegalStatus::class)],
            'vessel_type_id' => ['nullable', 'exists:vessel_types,id'],
            'home_port_id' => ['nullable', 'exists:ports,id'],
            'is_tuna_vessel' => ['nullable', 'boolean'],
            'construction_year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'beam_m' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'net_tonnage' => ['nullable', 'numeric', 'min:0'],
            'propulsion' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __("vessels::vessels.fields.{$field}")])
            ->all();
    }
}
