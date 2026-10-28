<?php

namespace Modules\Witnesses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedListDistribution;
use App\Models\AuthorizedListPublication;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Witnesses\Actions\RecordAuthorizedListDistribution;
use Modules\Witnesses\Http\Requests\DistributionRequest;

class DistributionController extends Controller
{
    public function index(): View
    {
        return view('witnesses::distributions', [
            'distributions' => AuthorizedListDistribution::with('publication')->latest('sent_at')->limit(100)->get(),
            'publications' => AuthorizedListPublication::orderByDesc('version')->get()->mapWithKeys(fn (AuthorizedListPublication $publication) => [$publication->id => 'v'.$publication->version.' — '.$publication->published_at->isoFormat('L')]),
        ]);
    }

    public function store(DistributionRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordAuthorizedListDistribution::class, $request->user(), $request->validated());

        return to_route('witnesses.distributions.index')->with('status', __('witnesses::distributions.recorded'));
    }
}
