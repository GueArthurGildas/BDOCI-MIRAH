<?php

namespace Modules\Missions\Http\Requests;

class EventRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'occurred_at' => ['nullable', 'date'],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
