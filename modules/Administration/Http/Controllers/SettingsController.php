<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Settings\SettingKey;
use App\Support\Settings\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Administration\Http\Requests\SettingsRequest;

class SettingsController extends Controller
{
    public function edit(Settings $settings): View
    {
        return view('administration::settings', [
            'keys' => SettingKey::cases(),
            'values' => collect(SettingKey::cases())->mapWithKeys(fn (SettingKey $key) => [$key->value => $settings->get($key)])->all(),
        ]);
    }

    public function update(SettingsRequest $request, Settings $settings): RedirectResponse
    {
        foreach (SettingKey::cases() as $key) {
            if ((string) $settings->get($key) !== (string) $request->validated($key->value)) {
                $settings->set($key, $request->validated($key->value));
            }
        }

        return to_route('administration.settings.edit')->with('status', __('administration::administration.settings.saved'));
    }
}
