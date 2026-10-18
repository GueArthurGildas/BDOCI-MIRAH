<?php

namespace Modules\Logbook\Http\Requests;

use App\Enums\ObservationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateObservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ObservationStatus::class)],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => __('logbook::logbook.fields.status'),
            'follow_up' => __('logbook::logbook.fields.follow_up'),
        ];
    }
}
