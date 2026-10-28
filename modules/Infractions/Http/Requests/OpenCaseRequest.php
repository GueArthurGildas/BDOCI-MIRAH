<?php

namespace Modules\Infractions\Http\Requests;

use App\Enums\InfractionQualification;
use Illuminate\Validation\Rule;

class OpenCaseRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['required', 'exists:vessels,id'],
            'infraction_type_ids' => ['nullable', 'array'],
            'infraction_type_ids.*' => ['integer', Rule::exists('infraction_types', 'id')->where('is_active', true)],
            'facts' => ['required', 'string', 'max:5000'],
            'qualification' => ['nullable', Rule::enum(InfractionQualification::class)],
            'captain_observations' => ['nullable', 'string', 'max:5000'],
            'inspection_id' => ['nullable', 'exists:external_inspections,id'],
            'mission_id' => ['nullable', 'exists:missions,id'],
            'operation_id' => ['nullable', 'exists:operations,id'],
            'witness_report_id' => ['nullable', 'exists:witness_reports,id'],
            'opened_at' => ['nullable', 'date'],
        ];
    }
}
