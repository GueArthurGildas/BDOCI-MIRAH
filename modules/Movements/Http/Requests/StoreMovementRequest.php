<?php

namespace Modules\Movements\Http\Requests;

use App\Enums\MovementDirection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vessel_id' => ['required', 'exists:vessels,id'],
            'port_id' => ['required', 'exists:ports,id'],
            'direction' => ['required', Rule::enum(MovementDirection::class)],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __('movements::movements.fields.'.str_replace('_id', '', $field))])
            ->all();
    }
}
