<?php

namespace Modules\Registry\Http\Controllers;

use App\Enums\SourceSystem;
use App\Http\Controllers\Controller;
use App\Support\Actions\ActionRunner;
use App\Support\Vessels\SourcePriorityResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Registry\Actions\UpdateSourcePriorities;

class SourcePriorityController extends Controller
{
    public function edit(SourcePriorityResolver $priorities): View
    {
        return view('registry::priorities', [
            'fields' => config('registry.tracked_vessel_fields'),
            'sources' => SourceSystem::cases(),
            'priorities' => $priorities,
        ]);
    }

    public function update(Request $request, ActionRunner $actions): RedirectResponse
    {
        $fields = config('registry.tracked_vessel_fields');
        $rules = [];

        foreach ($fields as $field) {
            foreach (SourceSystem::cases() as $source) {
                $rules["ranks.{$field}.{$source->value}"] = ['required', 'integer', 'between:1,99'];
            }
        }

        $data = $request->validate($rules);

        $actions->run(UpdateSourcePriorities::class, $request->user(), ['ranks' => $data['ranks']]);

        return to_route('registry.priorities.edit')->with('status', __('registry::registry.priorities.saved'));
    }
}
