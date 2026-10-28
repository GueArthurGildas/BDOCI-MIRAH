<?php

namespace Modules\Infractions\Http\Requests;

use App\Enums\InfractionSeverity;
use Illuminate\Validation\Rule;

class TypeRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40', Rule::unique('infraction_types', 'code')->ignore($this->route('type'))],
            'label' => ['required', 'string', 'max:255'],
            'legal_basis' => ['nullable', 'string', 'max:255'],
            'severity' => ['nullable', Rule::enum(InfractionSeverity::class)],
            'potential_penalty' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
