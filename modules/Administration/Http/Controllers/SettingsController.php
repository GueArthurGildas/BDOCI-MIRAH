<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Settings\SettingDefinition;
use App\Support\Settings\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Settings $settings): View
    {
        return view('administration::settings', [
            'settings' => $settings,
            'groups' => $settings->definitions()->groupBy('group')->map(fn (Collection $definitions) => $definitions->keyBy(fn (SettingDefinition $definition) => $definition->field())),
        ]);
    }

    public function update(Request $request, Settings $settings): RedirectResponse
    {
        $validated = $request->validate($settings->rules(), [], $this->attributes($settings));

        $settings->save($validated, $request->user());

        return to_route('administration.settings')->with('status', __('administration::settings.saved'));
    }

    public function reset(Settings $settings): RedirectResponse
    {
        $settings->reset();

        return to_route('administration.settings')->with('status', __('administration::settings.reset_done'));
    }

    /** @return array<string, string> */
    private function attributes(Settings $settings): array
    {
        return $settings->definitions()
            ->mapWithKeys(fn (SettingDefinition $definition, string $field) => [$field => __("administration::settings.fields.{$field}")])
            ->all();
    }
}
