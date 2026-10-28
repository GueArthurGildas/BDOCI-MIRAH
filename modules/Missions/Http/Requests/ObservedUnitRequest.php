<?php

namespace Modules\Missions\Http\Requests;

use App\Enums\ObservedUnitLevel;
use App\Enums\SightingSource;
use Illuminate\Validation\Rule;

class ObservedUnitRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'level' => ['required', Rule::enum(ObservedUnitLevel::class)],
            'sighting_source' => ['nullable', Rule::enum(SightingSource::class)],
            'vessel_id' => ['nullable', 'exists:vessels,id'],
            'recorded_name' => ['nullable', 'string', 'max:190'],
            'recorded_marks' => ['nullable', 'string', 'max:160'],
            'observed_activity' => ['nullable', 'string', 'max:160'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'observed_at' => ['nullable', 'date'],
            'inspection_id' => ['nullable', 'exists:external_inspections,id'],
        ];
    }
}
