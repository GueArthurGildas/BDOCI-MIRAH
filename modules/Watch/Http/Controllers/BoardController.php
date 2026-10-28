<?php

namespace Modules\Watch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Models\WatchEntry;
use App\Queries\MorningBoard;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Watch\Actions\AddVesselToDirectorList;
use Modules\Watch\Actions\CloseWatchEntry;
use Modules\Watch\Actions\PlaceVesselUnderEnhancedMonitoring;
use Modules\Watch\Http\Requests\VesselWatchRequest;

class BoardController extends Controller
{
    public function index(Request $request): View
    {
        return view('watch::board', [
            'entries' => MorningBoard::watchEntries(),
            'instructions' => MorningBoard::standingInstructions(),
            'observations' => MorningBoard::openObservations(),
            'relays' => MorningBoard::pendingRelaysFor($request->user()),
            'checkAlerts' => MorningBoard::checkAlerts(),
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
        ]);
    }

    public function addToDirectorList(VesselWatchRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AddVesselToDirectorList::class, $request->user(), $request->validated());

        return to_route('watch.board.index')->with('status', __('watch::watch.board.director_list_added'));
    }

    public function placeUnderEnhancedMonitoring(VesselWatchRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(PlaceVesselUnderEnhancedMonitoring::class, $request->user(), [...$request->validated(), 'origin' => $request->input('origin')]);

        return to_route('watch.board.index')->with('status', __('watch::watch.board.enhanced_monitoring_started'));
    }

    public function close(WatchEntry $entry, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CloseWatchEntry::class, $request->user(), ['watch_entry_id' => $entry->id]);

        return to_route('watch.board.index')->with('status', __('watch::watch.board.closed'));
    }
}
