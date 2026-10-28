<?php

namespace Modules\Communications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiAgentRequest extends FormRequest
{
    public function rules(): array
    {
        return ['enabled' => ['required', 'boolean']];
    }
}
