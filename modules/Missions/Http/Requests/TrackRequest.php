<?php

namespace Modules\Missions\Http\Requests;

class TrackRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:gpx,xml', 'max:20480'],
        ];
    }
}
