<?php

namespace Modules\Administration\Http\Requests;

use App\Support\Settings\SettingKey;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return collect(SettingKey::cases())
            ->mapWithKeys(fn (SettingKey $key) => [$key->value => $key->rules()])
            ->all();
    }

    public function attributes(): array
    {
        return collect(SettingKey::cases())
            ->mapWithKeys(fn (SettingKey $key) => [$key->value => __($key->label())])
            ->all();
    }
}
