<?php

namespace Modules\Vessels\Http\Controllers;

use App\Enums\MatchingStatus;
use App\Http\Controllers\Controller;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Vessels\Actions\IngestVesselSource;
use Modules\Vessels\Http\Requests\IngestVesselSourceRequest;

class VesselIngestController extends Controller
{
    public function create(): View
    {
        return view('vessels::ingest');
    }

    public function store(IngestVesselSourceRequest $request, ActionRunner $actions): RedirectResponse
    {
        $link = $actions->run(IngestVesselSource::class, $request->user(), $request->record());

        if ($link->matching_status === MatchingStatus::PossibleDuplicate) {
            return to_route('vessels.duplicates.index')->with('status', __('vessels::vessels.ingest.flagged'));
        }

        return to_route('vessels.show', $link->vessel_id)->with('status', __('vessels::vessels.ingest.result.'.$link->matching_status->value));
    }
}
