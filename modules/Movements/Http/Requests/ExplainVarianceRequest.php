<?php

namespace Modules\Movements\Http\Requests;

use App\Enums\EntryExitExplanation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExplainVarianceRequest extends FormRequest
{
    public function rules(): array
    {
        return ['explanation' => ['required', Rule::enum(EntryExitExplanation::class)]];
    }
}
