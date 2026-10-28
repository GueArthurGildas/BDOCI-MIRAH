<?php

namespace Modules\Vms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignBeaconRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['required', 'exists:vessels,id'],
            'beacon_id' => ['required', 'exists:beacons,id'],
            'started_on' => ['nullable', 'date'],
            'replacement_reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'vessel_id' => __('vms::vms.fields.vessel'),
            'beacon_id' => __('vms::vms.beacons.serial_number'),
            'started_on' => __('vms::vms.beacons.started_on'),
            'replacement_reason' => __('vms::vms.beacons.replacement_reason'),
        ];
    }
}
