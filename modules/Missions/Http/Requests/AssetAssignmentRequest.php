<?php

namespace Modules\Missions\Http\Requests;

class AssetAssignmentRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'asset_id' => ['required', 'exists:assets,id'],
            'planned_fuel_l' => ['nullable', 'numeric', 'min:0'],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
