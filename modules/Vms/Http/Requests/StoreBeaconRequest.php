<?php

namespace Modules\Vms\Http\Requests;

use App\Enums\BeaconModem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBeaconRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'serial_number' => ['required', 'string', 'max:80', 'unique:beacons,serial_number'],
            'model' => ['nullable', 'string', 'max:80'],
            'manufacturer' => ['nullable', 'string', 'max:80'],
            'supplier' => ['nullable', 'string', 'max:80'],
            'modem' => ['nullable', Rule::enum(BeaconModem::class)],
            'subscription' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function attributes(): array
    {
        return [
            'serial_number' => __('vms::vms.beacons.serial_number'),
            'model' => __('vms::vms.beacons.model'),
            'manufacturer' => __('vms::vms.beacons.manufacturer'),
            'supplier' => __('vms::vms.beacons.supplier'),
            'modem' => __('vms::vms.beacons.modem'),
            'subscription' => __('vms::vms.beacons.subscription'),
        ];
    }
}
