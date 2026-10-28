<?php

namespace Modules\Tracking\Http\Controllers;

use App\Enums\SlotResult;
use App\Enums\SlotTestResult;
use App\Http\Controllers\Controller;
use App\Models\ControlSlot;
use App\Models\SlotPosition;
use App\Support\Actions\ActionRunner;
use App\Support\Vms\ThemisLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Tracking\Actions\ConfirmSlotPosition;
use Modules\Tracking\Actions\OpenControlSlot;
use Modules\Tracking\Actions\RunSweep;
use Modules\Tracking\Http\Requests\ConfirmSlotPositionRequest;

class SlotController extends Controller
{
    private const PER_PAGE = 15;

    public function index(): View
    {
        return view('tracking::slots.index', [
            'slots' => ControlSlot::query()->withCount('slotPositions')->with('operator')->latest('started_at')->paginate(self::PER_PAGE),
        ]);
    }

    public function store(Request $request, ActionRunner $actions): RedirectResponse
    {
        $slot = $actions->run(OpenControlSlot::class, $request->user());

        return to_route('tracking.slots.show', $slot)->with('status', __('tracking::tracking.slots.opened'));
    }

    public function show(ControlSlot $slot, ThemisLink $themis): View
    {
        $positions = $slot->slotPositions()->with(['vessel', 'zone', 'locality'])->get()
            ->sortBy(fn (SlotPosition $position) => [$position->result === null ? 0 : 1, $position->emission_test === SlotTestResult::Ko ? 0 : 1, $position->vessel->name]);

        return view('tracking::slots.show', [
            'slot' => $slot->load(['operator', 'team', 'sweeps.anomalies.vessel']),
            'positions' => $positions->values(),
            'zoneCounts' => $slot->zoneCounts()->with('zone')->get()->sortByDesc('vessels_count'),
            'localityCounts' => $positions->whereNotNull('locality_id')->countBy(fn (SlotPosition $position) => $position->locality->label),
            'themis' => $themis,
            'testOptions' => SlotTestResult::options(),
            'resultOptions' => SlotResult::options(),
        ]);
    }

    public function confirm(ControlSlot $slot, SlotPosition $position, ConfirmSlotPositionRequest $request, ActionRunner $actions): RedirectResponse
    {
        abort_unless($position->control_slot_id === $slot->id, 404);

        $actions->run(ConfirmSlotPosition::class, $request->user(), [...$request->validated(), 'slot_position_id' => $position->id]);

        return to_route('tracking.slots.show', $slot)->with('status', __('tracking::tracking.slots.confirmed'));
    }

    public function sweep(ControlSlot $slot, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RunSweep::class, $request->user(), ['control_slot_id' => $slot->id]);

        return to_route('tracking.slots.show', $slot)->with('status', __('tracking::tracking.slots.swept'));
    }
}
