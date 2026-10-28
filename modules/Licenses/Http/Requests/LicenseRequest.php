<?php

namespace Modules\Licenses\Http\Requests;

use App\Enums\LicenseSegment;
use App\Models\License;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicenseRequest extends FormRequest
{
    public function rules(): array
    {
        $license = $this->route('license');

        return [
            'vessel_id' => [$license ? 'prohibited' : 'required', Rule::exists('vessels', 'id')->whereNull('deleted_at')],
            'number' => ['required', 'string', 'max:80', Rule::unique('licenses', 'number')->ignore($license instanceof License ? $license->id : null)],
            'segment' => ['nullable', Rule::enum(LicenseSegment::class)],
            'issued_on' => ['nullable', 'date'],
            'expires_on' => ['required', 'date', 'after_or_equal:issued_on'],
            'zone_ids' => ['nullable', 'array'],
            'zone_ids.*' => ['integer', 'exists:zones,id'],
            'gear_ids' => ['nullable', 'array'],
            'gear_ids.*' => ['integer', 'exists:gears,id'],
            'species_ids' => ['nullable', 'array'],
            'species_ids.*' => ['integer', 'exists:species,id'],
            'conditions' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return collect(['vessel_id' => 'vessel', 'number' => 'number', 'segment' => 'segment', 'issued_on' => 'issued_on', 'expires_on' => 'expires_on'])
            ->map(fn (string $field) => __("licenses::licenses.fields.{$field}"))
            ->all();
    }

    /** @return array<string, mixed> */
    public function actionInput(): array
    {
        $data = $this->validated();
        $data['conditions'] = preg_split('/\R/', (string) ($data['conditions'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $data['zone_ids'] ??= [];
        $data['gear_ids'] ??= [];
        $data['species_ids'] ??= [];

        return $data;
    }
}
