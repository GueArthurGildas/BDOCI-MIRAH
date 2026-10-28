<?php

namespace Modules\Operations\Http\Requests;

use App\Enums\OperationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([OperationStatus::Open->value, OperationStatus::InProgress->value])],
            'origin' => ['nullable', 'string', 'max:60'],
            'summary' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
