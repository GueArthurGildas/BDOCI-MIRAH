<?php

namespace Modules\Infractions\Http\Requests;

class AppealOutcomeRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'outcome' => ['required', 'string', 'max:255'],
        ];
    }
}
