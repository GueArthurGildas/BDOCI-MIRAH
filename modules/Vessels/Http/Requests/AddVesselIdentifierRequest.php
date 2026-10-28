<?php

namespace Modules\Vessels\Http\Requests;

use App\Enums\IdentifierType;
use App\Models\VesselIdentifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddVesselIdentifierRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(IdentifierType::class)],
            'value' => [
                'required', 'string', 'max:60',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $clash = VesselIdentifier::where('type', $this->input('type'))->where('value', $value)->whereNull('valid_to')
                        ->where('vessel_id', '!=', $this->route('vessel')->id)->exists();

                    if ($clash) {
                        $fail(__('vessels::vessels.identifiers.already_used'));
                    }
                },
            ],
            'valid_from' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return ['type' => __('vessels::vessels.identifiers.type'), 'value' => __('vessels::vessels.identifiers.value'), 'valid_from' => __('vessels::vessels.identifiers.valid_from')];
    }
}
