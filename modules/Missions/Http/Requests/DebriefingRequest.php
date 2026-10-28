<?php

namespace Modules\Missions\Http\Requests;

class DebriefingRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'held_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
