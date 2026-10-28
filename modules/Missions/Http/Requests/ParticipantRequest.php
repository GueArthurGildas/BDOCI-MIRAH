<?php

namespace Modules\Missions\Http\Requests;

class ParticipantRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'agent_id' => ['nullable', 'exists:agents,id'],
            'role' => ['nullable', 'string', 'max:60'],
            'agency' => ['nullable', 'string', 'max:120'],
            'grade' => ['nullable', 'string', 'max:80'],
            'is_mission_lead' => ['nullable', 'boolean'],
        ];
    }
}
