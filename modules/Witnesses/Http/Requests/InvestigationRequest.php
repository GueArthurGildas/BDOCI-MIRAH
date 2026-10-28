<?php

namespace Modules\Witnesses\Http\Requests;

use App\Enums\InvestigationElement;
use Illuminate\Validation\Rule;

class InvestigationRequest extends WitnessesRequest
{
    public function rules(): array
    {
        return [
            'elements' => ['nullable', 'array'],
            'elements.*' => [Rule::enum(InvestigationElement::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
