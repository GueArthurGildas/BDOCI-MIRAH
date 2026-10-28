<?php

namespace Modules\Witnesses\Http\Requests;

use App\Enums\ReportChannel;
use App\Enums\WitnessCategory;
use Illuminate\Validation\Rule;

class RecordReportRequest extends WitnessesRequest
{
    public function rules(): array
    {
        return [
            'received_at' => ['nullable', 'date'],
            'channel' => ['required', Rule::enum(ReportChannel::class)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'witness_id' => ['nullable', 'exists:witnesses,id'],
            'witness_name' => ['nullable', 'string', 'max:190'],
            'witness_category' => ['nullable', Rule::enum(WitnessCategory::class)],
            'witness_email' => ['nullable', 'email', 'max:190'],
            'witness_phone' => ['nullable', 'string', 'max:40'],
            'vessels' => ['nullable', 'array', 'max:2'],
            'vessels.*.name' => ['nullable', 'string', 'max:190'],
            'vessels.*.flag' => ['nullable', 'string', 'max:80'],
            'vessels.*.registration_port' => ['nullable', 'string', 'max:120'],
            'vessels.*.type' => ['nullable', 'string', 'max:80'],
            'vessels.*.ircs' => ['nullable', 'string', 'max:20'],
            'vessels.*.registration' => ['nullable', 'string', 'max:40'],
            'vessels.*.other_marks' => ['nullable', 'string', 'max:160'],
            'vessels.*.gear' => ['nullable', 'string', 'max:80'],
            'vessels.*.is_fishing' => ['nullable', 'boolean'],
            'vessels.*.route' => ['nullable', 'string', 'max:120'],
            'vessels.*.is_drifting' => ['nullable', 'boolean'],
            'vessels.*.is_resupplying' => ['nullable', 'boolean'],
            'vessels.*.is_transshipping' => ['nullable', 'boolean'],
            'vessels.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'vessels.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'vessels.*.description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
