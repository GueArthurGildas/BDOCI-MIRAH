<?php

namespace Modules\Communications\Http\Requests;

class RequestAiNoteRequest extends StoreNoteRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['instruction' => $this->input('instruction', $this->input('free_text'))]);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'instruction' => ['required', 'string', 'max:2000'],
        ];
    }

    public function payload(): array
    {
        return [...parent::payload(), 'instruction' => $this->validated('instruction')];
    }
}
