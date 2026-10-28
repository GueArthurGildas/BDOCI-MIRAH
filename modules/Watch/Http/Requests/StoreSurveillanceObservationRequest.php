<?php

namespace Modules\Watch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveillanceObservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['required', 'exists:vessels,id'],
            'slot_position_id' => ['nullable', 'exists:slot_positions,id'],
            'body' => ['required', 'string', 'max:5000'],
            'relay_to_team_id' => ['nullable', 'exists:teams,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'vessel_id' => __('watch::watch.fields.vessel'),
            'body' => __('watch::watch.fields.body'),
            'relay_to_team_id' => __('watch::watch.fields.relay_to'),
        ];
    }
}
