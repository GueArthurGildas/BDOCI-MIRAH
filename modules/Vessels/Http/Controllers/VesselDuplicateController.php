<?php

namespace Modules\Vessels\Http\Controllers;

use App\Enums\DuplicateDecision;
use App\Enums\MatchingStatus;
use App\Http\Controllers\Controller;
use App\Models\VesselSource;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Vessels\Actions\SettleVesselDuplicate;

class VesselDuplicateController extends Controller
{
    public function index(): View
    {
        $sources = VesselSource::with(['vessel.flagCountry', 'vessel.activeIdentifiers', 'candidate.flagCountry', 'candidate.activeIdentifiers'])
            ->where('matching_status', MatchingStatus::PossibleDuplicate->value)
            ->oldest()
            ->paginate(20);

        return view('vessels::duplicates', compact('sources'));
    }

    public function settle(Request $request, VesselSource $source, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', Rule::enum(DuplicateDecision::class)]]);

        $actions->run(SettleVesselDuplicate::class, $request->user(), ['vessel_source_id' => $source->id, 'decision' => $data['decision']]);

        return to_route('vessels.duplicates.index')->with('status', __('vessels::vessels.duplicates.settled'));
    }
}
