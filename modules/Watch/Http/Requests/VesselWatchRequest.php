<?php

namespace Modules\Watch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VesselWatchRequest extends FormRequest
{
    public function rules(): array
    {
        return ['vessel_id' => ['required', 'exists:vessels,id']];
    }

    public function attributes(): array
    {
        return ['vessel_id' => __('watch::watch.fields.vessel')];
    }
}
