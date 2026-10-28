<?php

namespace Modules\Licenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportLineRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'number' => ['nullable', 'string', 'max:80'],
            'segment' => ['nullable', 'string', 'max:40'],
            'issued_on' => ['nullable', 'string', 'max:10'],
            'expires_on' => ['nullable', 'string', 'max:10'],
            'zones' => ['nullable', 'string', 'max:500'],
            'gears' => ['nullable', 'string', 'max:500'],
            'species' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, mixed> */
    public function lineData(): array
    {
        $data = collect($this->validated())->map(fn ($value) => (string) $value);

        return [
            ...$data->only(['number', 'segment', 'issued_on', 'expires_on'])->map(fn (string $value) => trim($value))->all(),
            ...$data->only(['zones', 'gears', 'species'])->map(fn (string $value) => array_values(array_filter(array_map('trim', explode(',', $value)))))->all(),
        ];
    }
}
