<?php

namespace Modules\Watch\Http\Requests;

use App\Enums\SurveillanceObservationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetObservationStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return ['status' => ['required', Rule::enum(SurveillanceObservationStatus::class)]];
    }
}
