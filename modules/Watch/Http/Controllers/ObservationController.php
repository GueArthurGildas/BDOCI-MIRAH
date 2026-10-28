<?php

namespace Modules\Watch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CheckCounter;
use App\Models\SurveillanceObservation;
use App\Models\Team;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Watch\Actions\RecordSurveillanceObservation;
use Modules\Watch\Actions\SetSurveillanceObservationStatus;
use Modules\Watch\Http\Requests\SetObservationStatusRequest;
use Modules\Watch\Http\Requests\StoreSurveillanceObservationRequest;

class ObservationController extends Controller
{
    private const PER_PAGE = 20;

    public function index(): View
    {
        return view('watch::observations.index', [
            'observations' => SurveillanceObservation::query()->with(['vessel', 'author'])->latest('created_at')->paginate(self::PER_PAGE),
        ]);
    }

    public function create(Request $request): View
    {
        return view('watch::observations.create', [
            'slotPositionId' => $request->integer('slot_position_id') ?: null,
            'selectedVesselId' => $request->integer('vessel_id') ?: null,
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'teams' => Team::whereKeyNot($request->user()->team_id)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(StoreSurveillanceObservationRequest $request, ActionRunner $actions): RedirectResponse
    {
        $observation = $actions->run(RecordSurveillanceObservation::class, $request->user(), $request->validated());
        $counter = CheckCounter::find($observation->vessel_id);

        $redirect = to_route('watch.board.index')->with('status', __('watch::watch.observations.created'));

        return $counter->is_alert_raised
            ? $redirect->with('error', __('watch::watch.observations.threshold_exceeded', ['count' => $counter->checks_count]))
            : $redirect;
    }

    public function updateStatus(SetObservationStatusRequest $request, SurveillanceObservation $observation, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SetSurveillanceObservationStatus::class, $request->user(), [...$request->validated(), 'observation_id' => $observation->id]);

        return back()->with('status', __('watch::watch.observations.updated'));
    }
}
