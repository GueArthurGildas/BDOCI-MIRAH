<?php

namespace Modules\Vessels\Http\Requests;

use App\Enums\ActorRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddVesselActorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(ActorRole::class)],
            'legal_entity_id' => ['nullable', 'required_without:person_id', 'exists:legal_entities,id'],
            'person_id' => ['nullable', 'required_without:legal_entity_id', 'exists:people,id'],
            'started_on' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __("vessels::vessels.actors.fields.{$field}")])
            ->all();
    }
}
