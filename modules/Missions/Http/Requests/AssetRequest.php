<?php

namespace Modules\Missions\Http\Requests;

use App\Enums\AssetType;
use Illuminate\Validation\Rule;

class AssetRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AssetType::class)],
            'label' => ['required', 'string', 'max:160'],
        ];
    }
}
