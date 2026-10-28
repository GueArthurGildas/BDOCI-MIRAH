<?php

namespace Modules\Administration\Http\Controllers;

use App\Enums\ExternalLinkSource;
use App\Http\Controllers\Controller;
use App\Models\ExternalLink;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Administration\Actions\UpdateExternalLink;

class ExternalLinkController extends Controller
{
    public function edit(): View
    {
        $links = ExternalLink::all()->keyBy(fn (ExternalLink $link) => $link->source->value);

        return view('administration::connectors.links', ['sources' => ExternalLinkSource::cases(), 'links' => $links]);
    }

    public function update(Request $request, ActionRunner $actions): RedirectResponse
    {
        $rules = [];

        foreach (ExternalLinkSource::cases() as $source) {
            $rules["links.{$source->value}.model_url"] = ['nullable', 'string', 'max:500', 'regex:#^https?://\S+$#'];
            $rules["links.{$source->value}.is_copy_fallback"] = ['required', 'boolean'];
        }

        $validated = $request->validate($rules);

        foreach ($validated['links'] as $source => $values) {
            $actions->run(UpdateExternalLink::class, $request->user(), [...$values, 'source' => $source]);
        }

        return to_route('administration.connectors.links')->with('status', __('administration::administration.connectors.links.saved'));
    }
}
