<?php

namespace Modules\Missions\Http\Requests;

use App\Enums\MissionNature;
use App\Enums\MissionType;
use Illuminate\Validation\Rule;

class ScheduleMissionRequest extends MissionRequest
{
    public function rules(): array
    {
        return [
            'nature' => ['required', Rule::enum(MissionNature::class)],
            'type' => ['required', Rule::enum(MissionType::class)],
            'origin' => ['nullable', 'string', 'max:80'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'planned_start_at' => ['required', 'date'],
            'planned_end_at' => ['nullable', 'date', 'after_or_equal:planned_start_at'],
            'zone_ids' => ['nullable', 'array'],
            'zone_ids.*' => ['integer', 'exists:zones,id'],
            'points_gps' => ['nullable', 'string', 'max:5000'],
            'agent_ids' => ['nullable', 'array'],
            'agent_ids.*' => ['integer', 'exists:agents,id'],
            'lead_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
            'asset_ids' => ['nullable', 'array'],
            'asset_ids.*' => ['integer', 'exists:assets,id'],
            'operation_id' => ['nullable', 'exists:operations,id'],
            'witness_report_id' => ['nullable', 'exists:witness_reports,id'],
        ];
    }
}
