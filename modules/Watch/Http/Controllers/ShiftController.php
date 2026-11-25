<?php

namespace Modules\Watch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Relay;
use App\Models\Rotation;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Watch\Actions\AcknowledgeRelay;
use Modules\Watch\Actions\PlanRotations;
use Modules\Watch\Http\Requests\PlanRotationsRequest;

class ShiftController extends Controller
{
    public function index(): View
    {
        return view('watch::rotations.index', [
            'rotations' => Rotation::query()
                ->with(['team.lead'])
                ->whereDate('day', '>=', today()->subDays(7))
                ->orderBy('day')
                ->get(),
        ]);
    }

    public function plan(PlanRotationsRequest $request, ActionRunner $actions): RedirectResponse
    {
        $created = $actions->run(PlanRotations::class, $request->user(), $request->validated());

        return to_route('watch.rotations.index')->with('status', trans_choice('watch::watch.rotations.planned', $created->count()));
    }

    public function acknowledge(Relay $relay, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AcknowledgeRelay::class, $request->user(), ['relay_id' => $relay->id]);

        return to_route('watch.board.index')->with('status', __('watch::watch.board.relay_acknowledged'));
    }
}
