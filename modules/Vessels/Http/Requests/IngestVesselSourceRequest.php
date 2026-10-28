<?php

namespace Modules\Vessels\Http\Requests;

use App\Enums\FleetSegment;
use App\Enums\SourceSystem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngestVesselSourceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'source' => ['required', Rule::enum(SourceSystem::class)],
            'external_id' => ['nullable', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:190'],
            'flag' => ['nullable', Rule::in(config('surveillance.flag_states'))],
            'imo' => ['nullable', 'string', 'max:20'],
            'mmsi' => ['nullable', 'string', 'max:20'],
            'ircs' => ['nullable', 'string', 'max:20'],
            'registration' => ['nullable', 'string', 'max:50'],
            'fleet_segment' => ['nullable', Rule::enum(FleetSegment::class)],
            'length_m' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'gross_tonnage' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __("vessels::vessels.ingest.fields.{$field}")])
            ->all();
    }

    /** @return array<string, mixed> */
    public function record(): array
    {
        $data = $this->validated();

        return [
            'source' => $data['source'],
            'external_id' => $data['external_id'] ?? null,
            'name' => $data['name'],
            'flag' => $data['flag'] ?? null,
            'identifiers' => array_filter([
                'imo' => $data['imo'] ?? null,
                'mmsi' => $data['mmsi'] ?? null,
                'ircs' => $data['ircs'] ?? null,
                'registration' => $data['registration'] ?? null,
            ]),
            'attributes' => array_filter([
                'fleet_segment' => $data['fleet_segment'] ?? null,
                'length_m' => $data['length_m'] ?? null,
                'gross_tonnage' => $data['gross_tonnage'] ?? null,
            ], filled(...)),
        ];
    }
}
