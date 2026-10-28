<?php

namespace Modules\Infractions\Http\Requests;

use Illuminate\Validation\Rule;

class UpholdRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'infraction_type_id' => ['required', Rule::exists('infraction_types', 'id')->where('is_active', true)],
        ];
    }
}
