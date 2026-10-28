<?php

namespace Modules\Infractions\Http\Requests;

use App\Enums\PrecautionaryMeasureType;
use Illuminate\Validation\Rule;

class MeasureRequest extends InfractionRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PrecautionaryMeasureType::class)],
        ];
    }
}
