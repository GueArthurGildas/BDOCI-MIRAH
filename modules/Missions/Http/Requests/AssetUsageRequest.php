<?php

namespace Modules\Missions\Http\Requests;

class AssetUsageRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'engine_hours' => ['nullable', 'numeric', 'min:0'],
            'nautical_miles' => ['nullable', 'numeric', 'min:0'],
            'consumed_fuel_l' => ['nullable', 'numeric', 'min:0'],
            'state' => ['nullable', 'string', 'max:60'],
            'breakdown' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
