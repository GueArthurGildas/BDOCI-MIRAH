<?php

namespace Modules\Missions\Http\Requests;

class ObserverBoardingRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'observer_id' => ['nullable', 'exists:observers,id'],
            'name' => ['required_without:observer_id', 'nullable', 'string', 'max:190'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'vessel_id' => ['nullable', 'exists:vessels,id'],
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
        ];
    }
}
