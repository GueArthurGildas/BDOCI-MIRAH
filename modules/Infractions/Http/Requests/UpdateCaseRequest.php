<?php

namespace Modules\Infractions\Http\Requests;

use App\Enums\InfractionQualification;
use Illuminate\Validation\Rule;

class UpdateCaseRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'facts' => ['required', 'string', 'max:5000'],
            'qualification' => ['required', Rule::enum(InfractionQualification::class)],
            'captain_observations' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
