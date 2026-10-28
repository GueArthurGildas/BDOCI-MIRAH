<?php

namespace Modules\Infractions\Http\Requests;

use App\Enums\SeizureDecision;
use Illuminate\Validation\Rule;

class SeizureRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'item' => ['required', 'string', 'max:255'],
            'decision' => ['nullable', Rule::enum(SeizureDecision::class)],
        ];
    }
}
