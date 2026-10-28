<?php

namespace Modules\Missions\Http\Requests;

use App\Enums\MissionChecklistItem;
use Illuminate\Validation\Rule;

class BriefingRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'held_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['string', Rule::enum(MissionChecklistItem::class)],
        ];
    }
}
